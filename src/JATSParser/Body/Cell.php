<?php namespace JATSParser\Body;

use JATSParser\Body\JATSElement as JATSElement;
use JATSParser\Body\Text as Text;
use JATSParser\Body\Par as Par;
use JATSParser\Body\InlineEquation as InlineEquation;
use JATSParser\Body\Equation as Equation;

class Cell extends AbstractElement {

	/* @var array Can contain Par and Text */
	private $content = array();

	/* @var $type string  */
	private $type;

	/* @var $colspan int  */
	private $colspan;

	/* @var $rowspan int  */
	private $rowspan;

	/* @var $align string  */
	private $align;


	function __construct(\DOMElement $cellNode) {
		parent::__construct($cellNode);
		
		$this->type = $cellNode->nodeName;
		$content = array();
		$xpath = Document::getXpath();
		$childNodes = $xpath->query("child::node()", $cellNode);
		foreach ($childNodes as $childNode) {
			if ($childNode->nodeName === "p") {
				$par = new Par($childNode);
				$content[] = $par;
			} else if ($childNode->nodeName === "inline-formula") {
				$inlineEquation = new InlineEquation($childNode);
				$content[] = $inlineEquation;
			} else if ($childNode->nodeName === "disp-formula") {
				$equation = new Equation($childNode);
				$content[] = $equation;
			} else {
				$jatsTextNodes = $xpath->query(".//self::text()", $childNode);
				foreach ($jatsTextNodes as $jatsTextNode){
					$jatsText = new Text($jatsTextNode);
					$content[] = $jatsText;
				}
			}
		}

		$this->content = $content;

		$cellNode->hasAttribute("colspan") ? $this->colspan = $cellNode->getAttribute("colspan") : $this->colspan = 1;

		$cellNode->hasAttribute("rowspan") ? $this->rowspan = $cellNode->getAttribute("rowspan") : $this->rowspan = 1;
		
		$cellNode->hasAttribute("align") ? $this->align = $cellNode->getAttribute("align") : $this->align = "";
	}

	/**
	 * @return array
	 */

	public function getContent(): array {
		return $this->content;
	}

	/**
	 * @return string
	 */

	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getColspan(): int
	{
		return $this->colspan;
	}

	/**
	 * @return int
	 */
	public function getRowspan(): int
	{
		return $this->rowspan;
	}

	public function getAlign(): string
	{
		return $this->align;
	}
}
