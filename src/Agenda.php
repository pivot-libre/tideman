<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

class Agenda
{
    private $candidateSet;
    public function __construct(Ballot ...$ballots)
    {
        /**
         * @todo #7 populate $candidateSet with all of the Candidates in $BallotTest
         */


         $this->candidateSet = new \SplObjectStorage();
         echo $ballots;
         //might be an array, or might be SplObjectStorage
         //read this article, then decide
         // http://technosophos.com/2009/05/29/set-objects-php-arrays-vs-splobjectstorage.html
    }

    public function getCandidates() : CandidateList
    {

         $candidateList = new CandidateList();
         /**
          * @todo #7 iterate through all of the candidates in the set, return a CandidateList;
          */
         return $candidateList;
    }
}
