<?php namespace JATSParser\HTML;


use JATSParser\Back\AbstractReference;
use JATSParser\Back\Individual;
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
		if (!isset($this->content)) $this->content = new \stdClass();


		$this->setSimpleProperty('id', 'getId');

		if (!empty($this->jatsReference->getAuthors())) {
			foreach ($this->jatsReference->getAuthors() as $individual) {
				if (get_class($individual) == 'JATSParser\Back\Individual') { /** @var $individual Individual */
					$author = new \stdClass();
					if (!empty($individual->getGivenNames())) {
						$author->family = $individual->getSurname();
					}

					if (!empty($individual->getSurname())) {
						$author->given = $individual->getGivenNames();
					}

					$this->content->author[] = $author;

				}
			}
		}

		if (!empty($this->jatsReference->getEditors())) {
			foreach ($this->jatsReference->getEditors() as $individual) {
				if (get_class($individual) == 'JATSParser\Back\Individual') { /** @var $individual Individual */
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

		// specific properties
		$this->setDate('issued', 'getYear');
		$this->setSimpleProperty('container-title', 'getJournal');
		$this->setSimpleProperty('journal', 'getJournal');
		$this->setSimpleProperty('volume', 'getVolume');
		$this->setSimpleProperty('issue', 'getIssue');
		$this->setSimpleProperty('page-first', 'getFpage');
		$this->setSimpleProperty('page', 'getPages');

		if (method_exists($this->jatsReference, 'getPubIdType') && array_key_exists('doi', $this->jatsReference->getPubIdType())) {
			$this->content->{'DOI'} = $this->jatsReference->getPubIdType()['doi'];
		}

		$this->setSimpleProperty('publisher', 'getPublisherName');
		$this->setSimpleProperty('publisher-place', 'getPublisherLoc');
		$this->setSimpleProperty('container-title', 'getBook');
		$this->setSimpleProperty('event', 'getConfName');
		$this->setDate('event-date', 'getConfDate');
		$this->setSimpleProperty('event-place', 'getConfLoc');

		switch (get_class($this->jatsReference)) {

			case "JATSParser\Back\Journal":

				/* @var $jatsReference Journal */
				$this->content->type = 'article-journal';
				break;

			case "JATSParser\Back\Book":

				/* @var $jatsReference Book */
				$this->content->type = 'book';

				break;

			case "JATSParser\Back\Chapter":

				/* @var $jatsReference Chapter */
				$this->content->type = 'chapter';

				break;

			case "JATSParser\Back\Conference":

				/* @var $jatsReference Conference */
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
}
