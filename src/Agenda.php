<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

/**
 * This class should be used to represent all of the Candidates in an election
 */
class Agenda
{
    private $candidateList;

    /**
     * Creates an Agenda consisting of all unique Candidates from the parameterized Ballots.
     */
    public function __construct(Ballot ...$ballots)
    {
        $candidateSet = array();
        foreach ($ballots as $ballot) {
            //a ballot has multiple CandidateLists
            foreach ($ballot as $candidateList) {
                //a CandidateList has multiple Candidates
                foreach ($candidateList as $candidate) {
                    $candidateId = $candidate->getId();
                    $candidateSet[$candidateId] = $candidate;
                    /**
                     * Since we are only using the candidateId as the key, we will set the value to a new Candidate
                     * every time. That means that if the Ballots store differing information on the same Candidate,
                     * then the attributes of the Candidate most-recently iterated over will be used.
                     */
                }
            }
        }
        $this->candidateList = new CandidateList(...array_values($candidateSet));
    }

    /**
     * Returns all Candidates for this election as a CandidateList.
     * The order of the Candidates wihtin the list is not significant.
     */
    public function getCandidates() : CandidateList
    {
        return $this->candidateList;
    }
}
