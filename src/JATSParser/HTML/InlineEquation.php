<?php namespace JATSParser\HTML;

use DOMDocument;
use DOMNode;
use JATSParser\Body\InlineEquation as JATSInlineEquation;

class InlineEquation extends \DOMElement {

	public function __construct() {
		parent::__construct("span");
	}

	public function appendHTML(DOMNode $parent, $source) {
		$tmpDoc = new DOMDocument();
		@$tmpDoc->loadHTML('<?xml encoding="UTF-8">' .$source);
		foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
			$node = $parent->ownerDocument->importNode($node, true);
			$parent->appendChild($node);
		}
	}

	public function setContent(JATSInlineEquation $jatsEquation) {
		$this->appendHTML($this,$jatsEquation->getContent());
	}
}
