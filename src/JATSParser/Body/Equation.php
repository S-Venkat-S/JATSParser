<?php namespace JATSParser\Body;

class Equation extends AbstractElement {

	private $content = null;
	private $attrib;

	public function innerHTML($node) {
		return implode(array_map([$node->ownerDocument,"saveHTML"], 
								 iterator_to_array($node->childNodes)));
	}

	public function __construct(\DOMElement $element) {
		parent::__construct($element);
		// var_dump($element->c14n());
		$this->xpath->query("disp-formula", $element);
		$math = str_replace("mml:","",$this->innerHTML($element));
		$math = str_replace("mml-eqn-","eqn-",$math);
		$this->content = $math;
		$this->attrib = $this->extractFormattedText(".//id", $element);
	}

	public function getContent(): string {
		return $this->content;
	}

	public function getAttrib(): array {
		return $this->attrib;
	}
}
