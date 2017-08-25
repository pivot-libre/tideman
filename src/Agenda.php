<?php
namespace PivotLibre\Tideman;

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
        $this->candidateSet = new CandidateSet();
        $this->addCandidatesFromBallots(...$ballots);
    }

    public function removeCandidates(Candidate ...$candidates)
    {
        $this->candidateSet->remove(...$candidates);
    }

    public function addCandidatesFromBallots(Ballot ...$ballots)
    {
        foreach ($ballots as $ballot) {
            //a ballot has multiple CandidateLists
            foreach ($ballot as $candidateList) {
                //a CandidateList has multiple Candidates
                foreach ($candidateList as $candidate) {
                    $this->candidateSet->add($candidate);
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
     * The order of the Candidates within the returned list is not significant.
     */
    public function getCandidates() : CandidateList
    {
        return new CandidateList(...array_values($this->candidateSet->toArray()));
    }
}
