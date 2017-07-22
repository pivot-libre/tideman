<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

/**
 * This class should be used to represent all of the Candidates in an election
 */
class Agenda
{
    private $candidateSet;

    /**
     * Creates an Agenda consisting of all unique Candidates from the parameterized Ballots.
     */
    public function __construct(Ballot ...$ballots)
    {
        $this->candidateSet = new \SplObjectStorage();
        foreach ($ballots as $ballot) {
            foreach ($ballot as $candidateList) {
                foreach ($candidateList as $candidate) {
                    $candidateId = $candidate->getId();
                    $this->candidateSet->attach($candidateId, $candidate);
                    /**
                     * Since we are only using the candidateId as the key, we will set the value to a new Candidate
                     * every time. That means that if the Ballots store differing information on the same Candidate,
                     * then the attributes of the Candidate most-recently iterated over will be used.
                     */
                }
            }
        }
    }
    /**
     * Returns all Candidates for this election as a CandidateList.
     * The order of the Candidates wihtin the list is not significant.
     */
    public function getCandidates() : CandidateList
    {
        $candidates = array();
        $this->candidateSet->rewind();
        while ($this->candidateSet->valid()) {
            $candidate = $this->candidateSet->getInfo();
            $candidates[] = $candidate;
            $this->candidateSet->next();
        }
        $candidateList = new CandidateList(...$candidates);
        return $candidateList;
    }
}
