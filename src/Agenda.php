<?php
namespace PivotLibre\Tideman;

use \Countable;

/**
 * This class should be used to represent all of the Candidates in an election
 */
class Agenda implements Countable, \JsonSerializable
{
    private $candidateSet;

    /**
     * Creates an Agenda consisting of all unique Candidates from the parameterized Ballots.
     */
    public function __construct(NBallot ...$ballots)
    {
        $this->candidateSet = new CandidateSet();
        $this->addCandidatesFromBallots(...$ballots);
    }


    /**
     * @param Candidate ...$candidates
     */
    public function addCandidates(Candidate ...$candidates) : void
    {

        foreach ($candidates as $candidate) {
            $this->candidateSet->add($candidate);
            /**
             * Since we are only using the candidateId as the key, we will set the value to a new Candidate
             * every time. That means that if the Ballots store differing information on the same Candidate,
             * then the attributes of the Candidate most-recently iterated over will be used.
             */
        }
    }

    public function removeCandidates(Candidate ...$candidates)
    {
        $this->candidateSet->remove(...$candidates);
    }

    public function addCandidatesFromBallots(NBallot ...$ballots)
    {
        foreach ($ballots as $ballot) {
            //a ballot has multiple CandidateLists
            foreach ($ballot as $candidateList) {
                $this->addCandidates(...$candidateList);
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

    public function count() : int
    {
        return $this->candidateSet->count();
    }

    /**
     * @return mixed[] List of associative arrays that describe candidates
     */
    public function jsonSerialize()
    {
        $values = array_values($this->candidateSet->toArray());
        asort($values);
        return $values;
    }
}
