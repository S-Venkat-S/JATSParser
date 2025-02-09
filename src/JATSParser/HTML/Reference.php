<?php namespace JATSParser\HTML;


use JATSParser\Back\AbstractReference;
use JATSParser\Back\Individual;
use JATSParser\Back\Collaboration;
use JATSParser\Back\Journal;
use JATSParser\Back\Book;
use JATSParser\Back\Chapter;
use JATSParser\Back\Conference;


class Reference {

	/** @var $content \stdClass */
	private $content;
	private $jatsReference;

	public function __construct(AbstractReference $jatsReference) {
		$this->jatsReference = $jatsReference;
		$this->setContent();
	}

	public function setContent() {
		if (!isset($this->content)) {
			$this->content = new \stdClass();
		}
		
		$this->content->status = $this->content->status ?? 'active';
		// Set the ID property
		$this->setSimpleProperty('id', 'getId');
		if (!empty($this->jatsReference->getAuthors())) {
			foreach ($this->jatsReference->getAuthors() as $individual) {
				if (is_object($individual) && get_class($individual) == 'JATSParser\Back\Individual') {
					$author = new \stdClass();
					
					if (!empty($individual->getGivenNames())) {
						$author->given = $individual->getGivenNames();
					}
						if (!empty($individual->getSurname())) {
						$author->family = $individual->getSurname();
					}
					$this->content->author[] = $author;
				}
				if (is_object($individual) && get_class($individual) == 'JATSParser\Back\Collaboration') {
					
					$author = new \stdClass();
					
					
					if (!empty($individual->getName())) {
						$author->family = $individual->getName();
					}

					$this->content->author[] = $author;
				}
			}
		}
	
	
		if (!empty($this->jatsReference->getEditors())) {
			foreach ($this->jatsReference->getEditors() as $individual) {
				
				if (is_object($individual) && get_class($individual) == 'JATSParser\Back\Individual') {
				
					$editor = new \stdClass();
	
					
					if (!empty($individual->getGivenNames())) {
						$editor->family = $individual->getSurname();
					}
	
					
					if (!empty($individual->getSurname())) {
						$editor->given = $individual->getGivenNames();
					}
	
				
					$this->content->editor[] = $editor;
				}
			}
		}
	
		
		$this->setSimpleProperty('url', 'getUrl');
		$this->setSimpleProperty('title', 'getTitle');
	
		
		if (checkdate(1, 1, (int) $this->jatsReference->getYear())) {
			$this->setDate('issued', 'getYear');
		}
	
		
		$this->setSimpleProperty('container-title', 'getJournal');
		$this->setSimpleProperty('journal', 'getJournal');
		$this->setSimpleProperty('volume', 'getVolume');
		$this->setSimpleProperty('issue', 'getIssue');
		$this->setSimpleProperty('page-first', 'getFpage');
		$this->setSimpleProperty('page', 'getPages');
	
		// Check DOI and set it if available
		if (method_exists($this->jatsReference, 'getPubIdType') && array_key_exists('doi', $this->jatsReference->getPubIdType())) {
			$doi = $this->jatsReference->getPubIdType()['doi'];
			// Remove DOI prefix (if it exists)
			if (self::isDoiUrl($doi)) {
				$doi = substr_replace($doi, '', 0, strlen(DOI_REFERENCE_PREFIX));
			}
			$this->content->{'DOI'} = $doi;
		}
	
		// Set publisher and location
		$this->setSimpleProperty('publisher', 'getPublisherName');
		$this->setSimpleProperty('publisher-place', 'getPublisherLoc');
		$this->setSimpleProperty('container-title', 'getBook');
		$this->setSimpleProperty('event', 'getConfName');
		$this->setDate('event-date', 'getConfDate');
		$this->setSimpleProperty('event-place', 'getConfLoc');
	
		// Determine the type of reference (Journal, Book, Chapter, Conference)
		switch (get_class($this->jatsReference)) {
			case "JATSParser\Back\Journal":
				/** @var $jatsReference Journal */
				$this->content->type = 'article-journal';
				break;
	
			case "JATSParser\Back\Book":
				/** @var $jatsReference Book */
				$this->content->type = 'book';
				break;
	
			case "JATSParser\Back\Chapter":
				/** @var $jatsReference Chapter */
				$this->content->type = 'chapter';
				break;
	
			case "JATSParser\Back\Conference":
				/** @var $jatsReference Conference */
				$this->content->type = 'conference';
				break;
		}
	}
	

	/**
	 * @return array
	 */
	public function getContent(): \stdClass
	{
		return $this->content;
	}

	/**
	 * @param $property string JSON property
	 * @param $method string method to retrieve property from JATS Parser Reference
	 * @return void
	 */
	protected function setSimpleProperty(string $property, string $method): void {
		if (method_exists($this->jatsReference, $method) && !empty($this->jatsReference->$method())) {
			$this->content->{$property} = $this->jatsReference->$method();
		}
	}

	protected function setDate(string $property, string $method): void {
		if (method_exists($this->jatsReference, $method) && !empty($this->jatsReference->$method())) {
			$date = new \stdClass();
			$date->{'date-parts'}[][] = $this->jatsReference->$method();
			$this->content->{$property} = $date;
		}
	}

	/**
	 * @return bool
	 * @brief checks if generated CJSON-CSL doesn't contain ref specific info, e.g., title, authors, year.
	 * TODO find a better way of CSL validation
	 */
	public function refIsEmpty(): bool {
		$csl = (array) $this->content;
		// ID and type are assigned irrespectively to the reference content
		unset($csl['id']);
		unset($csl['type']);
		return empty($csl);
	}

	public function getJatsReference(): AbstractReference {
		return $this->jatsReference;
	}

	public static function isDoiUrl($doi) {
		return substr($doi, 0, strlen(DOI_REFERENCE_PREFIX)) === DOI_REFERENCE_PREFIX;
	}
}
