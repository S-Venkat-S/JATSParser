<?php namespace JATSParser\HTML;

use DOMDocument;
use DOMNode;
use JATSParser\Body\Equation as JATSEquation;
use JATSParser\HTML\Text as HTMLText;

class Equation extends \DOMElement {

	public function __construct($tag = "div") {
		parent::__construct($tag);
	}

	public function appendHTML(DOMNode $parent, $source) {
		$tmpDoc = new DOMDocument();
		@$tmpDoc->loadHTML('<?xml encoding="UTF-8">' .$source);
		foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
			$node = $parent->ownerDocument->importNode($node, true);
			$parent->appendChild($node);
		}
	}

	public function setContent(JATSEquation $jatsEquation) {
		$this->appendHTML($this,$jatsEquation->getContent());
	}
}
