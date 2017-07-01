<?php
namespace PivotLibre\Tideman;

class Ballot
{
    /**
     * @todo #4 Implement ballot logic
     */
	private $listOfListOfCandidates;

  	public function __construct(CandidateListList /*...*/$listOfListOfCandidates) {
		$this->listOfListOfCandidates = $listOfListOfCandidates;
	}


}
