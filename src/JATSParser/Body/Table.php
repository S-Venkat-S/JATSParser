<?php namespace JATSParser\Body;

use JATSParser\Body\JATSElement as JATSElement;
use JATSParser\Body\Document as Document;
use JATSParser\Body\Row as Row;
use JATSParser\Body\Text as Text;

class Table extends AbstractElement {

	/* @var $id string */
	private $id;

	/* @var $label string */
	private $label;

	/* @var $content array */
	private $content;

	/* @var $hasHead bool */
	private $hasHead;

	/* @var $hasBody bool */
	private $hasBody;

	/* @var $title array */
	private $title;

	/* @var $notes array */
	private $notes;

	/* @var $footnotes array */
	private $footnotes;

	public function __construct(\DOMElement $tableWraper) {
		parent::__construct($tableWraper);
		
		$this->label = $this->extractFromElement(".//label", $tableWraper);
		$this->link = $this->extractFromElement("./@xlink:href", $tableWraper);
		$this->id = $this->extractFromElement("./@id", $tableWraper);
		$this->title = $this->extractTitleOrCaption($tableWraper, self::JATS_EXTRACT_TITLE);
		$this->notes = $this->extractTitleOrCaption($tableWraper, self::JATS_EXTRACT_CAPTION);
		$this->footnotes = $this->extractTableWrapFoot($tableWraper); // Extract footnotes
		$this->extractContent($tableWraper);
	}

	public function getContent(): ?array {
		return $this->content;
	}

	public function getId(): ?string {
		return $this->id;
	}

	public function getLabel(): ?string {
		return $this->label;
	}

	public function getTitle(): ?array {
		return $this->title;
	}

	public function getNotes(): ?array {
		return $this->notes;
	}
	public function getFootnotes(): ?array {
		return $this->footnotes;
	}

	private function extractContent(\DOMElement $tableWraper) {
		$content = array();

		$tableHeadNode = $this->xpath->query(".//thead", $tableWraper);
		if ($tableHeadNode->length > 0) {
			$this->hasHead = TRUE;
		} else {
			$this->hasHead = FALSE;
		}

		$tableBodyNode = $this->xpath->query(".//tbody", $tableWraper);
		if ($tableBodyNode->length > 0) {
			$this->hasBody = TRUE;
		} else {
			$this->hasBody = FALSE;
		}

		$rowNodes = $this->xpath->query(".//tr", $tableWraper);
		foreach ($rowNodes as $rowNode) {
			$row = new Row($rowNode);
			$content[] = $row;
			$align = $rowNode->getAttribute("align");
			if (!empty($align)) {
				$row->setAlign($align); 
			}
		}
		$this->content = $content;
	}
	private function extractTableWrapFoot(\DOMElement $tableWraper): array {
		$footnotes = [];
		$footNodeList = $this->xpath->query(".//table-wrap-foot/fn", $tableWraper);

		foreach ($footNodeList as $fnNode) {
			$id = $this->extractFromElement("./@id", $fnNode);
			$type = $this->extractFromElement("./@fn-type", $fnNode);
			$content = $this->extractFromElement(".//p", $fnNode);

			$footnotes[] = [
				'id' => $id,
				'type' => $type,
				'content' => $content,
			];
		}

		return $footnotes;
	}
}
