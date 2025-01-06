<?php namespace JATSParser\Body;
use JATSParser\Body\JATSElement as JATSElement;
use JATSParser\Body\Document as Document;
use JATSParser\Body\Text as Text;
use JATSParser\Body\InlineEquation as InlineEquation;
use JATSParser\Body\Equation as Equation;

class Par implements JATSElement {

    private $content = array();
    private $blockElements = array();

    function __construct(\DOMElement $paragraph) {
		
        $xpath = Document::getXpath();
        
        // Find, set and exclude block elements from DOM
        $this->findExtractRemoveBlockElements($paragraph, $xpath);
        // Parse content
        $content = array();
        $parTextNodes = $xpath->query(".//text()|.//inline-formula|.//disp-formula", $paragraph);

        foreach ($parTextNodes as $parTextNode) {
            if ($parTextNode instanceof \DOMElement) {
                // var_dump($parTextNode->c14n());
                if ($parTextNode->tagName == "inline-formula") {
                    $jatsText = new InlineEquation($parTextNode);
                    $content[] = $jatsText;
                } elseif ($parTextNode->tagName == "disp-formula") {
                    $jatsText = new Equation($parTextNode);
                    $content[] = $jatsText;
                }
            } elseif ($parTextNode instanceof \DOMText && strpos($parTextNode->getNodePath(), "mml:math") === false) {
                $jatsText = new Text($parTextNode);
                $content[] = $jatsText;
            }
        }
        $this->content = $content;
    }

    public function getContent(): array {
        return $this->content;
    }

    public function getBlockElements() {
        return $this->blockElements;
    }

    /**
     * @param \DOMElement $paragraph
     * @param \DOMXPath $xpath
     * @brief Method aimed at finding block elements inside the paragraph, save as an array property and delete them from the DOM
     */
    private function findExtractRemoveBlockElements(\DOMElement $paragraph, \DOMXPath $xpath): void
    {
        // var_dump($paragraph->c14n());
        $expression = "";
        $blockNodesMappedArray = AbstractElement::mappedBlockElements();
        $lastKey = array_key_last($blockNodesMappedArray);
        foreach ($blockNodesMappedArray as $key => $nodeString) {
            $expression .= ".//" . $nodeString;
            if ($key !== $lastKey) {
                $expression .= "|";
            }
        }
        $blockElements = $xpath->query($expression, $paragraph);
        
        if (empty($blockElements)) return;

        foreach ($blockElements as $blockElement) {
            // var_dump($blockElement->tagName);
			if ($blockElement->tagName === 'disp-formula') {
				continue;
			}
            if ($className = array_search($blockElement->tagName, $blockNodesMappedArray)) {
                $className = "JATSParser\Body\\" . $className;
                $jatsBlockEl = new $className($blockElement);
                // var_dump($className);
                $this->blockElements[] = $jatsBlockEl;
            }
            // var_dump($blockElement->c14n());
            $blockElement->parentNode->removeChild($blockElement);
        }
    }
}
