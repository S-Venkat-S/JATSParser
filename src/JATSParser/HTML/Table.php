<?php namespace JATSParser\HTML;

use JATSParser\Body\Table as JATSTable;
use JATSParser\Body\Row as JATSRow;
use JATSParser\HTML\Cell as Cell;
use JATSParser\Body\Cell as JATSCell;
use JATSParser\HTML\Text as HTMLText;

class Table extends \DOMElement {

	public function __construct() {

		parent::__construct("table");

	}

	public function setContent(JATSTable $jatsTable) {

		// Converting table head
		$hasHead = false;
		$hasBody = false;

		$htmlHead = $this->ownerDocument->createElement("thead");
		$htmlBody = $this->ownerDocument->createElement("tbody");
		$htmlFoot = $this->ownerDocument->createElement("tfoot");

		foreach ($jatsTable->getContent() as $row) {
			/* @var $row JATSRow */
			switch ($row->getType()) {
				case 1:
					$hasHead = true;
					$hasHead = true;
					$htmlRow = $this->ownerDocument->createElement("tr");
					$htmlHead->appendChild($htmlRow);
					foreach ($row->getContent() as $cell) {
						/* @var $cell JATSCell */
						$htmlCell = new Cell($cell->getType());
						$htmlRow->appendChild($htmlCell);
						$htmlCell->setContent($cell);
					}
					break;
				case 2:
					$hasBody = true;
					$this->extractRowsAndCells($htmlBody, $row);
					break;
				case 3:
					$this->extractRowsAndCells($this, $row);
					break;
			}
		}

		if ($hasHead) {
			$this->appendChild($htmlHead);
		}

		if ($hasBody) {
			$this->appendChild($htmlBody);
		}
		
		// Retrieving caption
		$titleNode = $this->ownerDocument->createElement("caption");
		$this->appendChild($titleNode);

        // Set table id for table-wrap. Needed for links from referenceces to the table
        $this->setAttribute("id", $jatsTable->getId());
		
		// Set figure label (e.g., Figure 1)
		if ($jatsTable->getLabel()) {
			$spanLabel = $this->ownerDocument->createElement("span");
			$spanLabel->setAttribute("class", "label");
			$titleNode->appendChild($spanLabel);
			$textNode = $this->ownerDocument->createTextNode(HTMLText::checkPunctuation($jatsTable->getLabel()));
			$spanLabel->appendChild($textNode);
		}
		
		/* Set table title
        * @var $tableTitle JATSText
        */
		if (count($jatsTable->getTitle()) > 0) {
			$spanTitle = $this->ownerDocument->createElement("span");
			$spanTitle->setAttribute("class", "title");
			$titleNode->appendChild($spanTitle);
			foreach ($jatsTable->getTitle() as $tableTitle) {
				if (get_class($tableTitle) === "JATSParser\Body\Equation") {
					$equationGroup = new Equation("span");
					$spanTitle->appendChild($equationGroup);
					$equationGroup->setContent($tableTitle);
				} else if (get_class($tableTitle) === "JATSParser\Body\InlineEquation") {
					$InlineequationGroup = new InlineEquation();
					$spanTitle->appendChild($InlineequationGroup);
					$InlineequationGroup->setContent($tableTitle);
				} else {
					HTMLText::extractText($tableTitle, $spanTitle);
				}
			}
		}
		
		/* Set table notes
        * @var $jatsTable JATSPar
        */
		if (count($jatsTable->getNotes()) > 0) {
			foreach ($jatsTable->getNotes() as $tableContent) {
				$par = new Par("span");
				$titleNode->appendChild($par);
				$par->setAttribute("class", "notes");
				$par->setContent($tableContent);
			}
		}
		/* Set table foot notes
        * @var $jatsTable JATSPar
        */
		$footnotes = $jatsTable->getFootnotes();
		if (count($footnotes) > 0) {
			foreach ($footnotes as $footnote) {
				$footnoteRow = $this->ownerDocument->createElement("tr");
				$footnoteCell = $this->ownerDocument->createElement("td");
				$footnoteCell->setAttribute("colspan", "100%"); // To span across the table
	
				$footnoteText = $this->ownerDocument->createTextNode($footnote['content']);
				$footnoteCell->appendChild($footnoteText);
				$footnoteRow->appendChild($footnoteCell);
	
				$htmlFoot->appendChild($footnoteRow);
			}
			// Append the footnotes to the table
			$this->appendChild($htmlFoot);
		}

	}

	/**
	 * @param $htmlHead \DOMElement
	 * @param $row JATSRow
	 */
	private function extractRowsAndCells(\DOMElement $htmlElement, JATSRow $row): void
	{
		$htmlRow = $this->ownerDocument->createElement("tr");
		// $htmlRow->setAttribute("align","center");

		$align = $row->getAlign();
		if (!empty($align)) {
			$htmlRow->setAttribute("align", $align);
		}

		$htmlElement->appendChild($htmlRow);
		foreach ($row->getContent() as $cell) {
			/* @var $cell JATSCell */
			$htmlCell = new Cell($cell->getType());
			$htmlRow->appendChild($htmlCell);
			$htmlCell->setContent($cell);

		}
	}

}