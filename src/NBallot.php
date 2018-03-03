<?php
namespace PivotLibre\Tideman;

/**
 * This class shows how many times the same Ballot was submitted in an election.
 * This object could be populated by the result of a COUNT  SQL query like:
 * SELECT COUNT(ranking), ranking FROM ballots WHERE ranking = ...
 */
class NBallot extends Ballot
{
    private $count;
    public function __construct(int $count, CandidateList ...$listsOfCandidates)
    {
        parent::__construct(...$listsOfCandidates);
        $this->count = $count;
    }
    public function getCount()
    {
        return $this->count;
    }
    /**
     * @eturn a copy of the current instance whose tied candidates have been put
     * in a random order. This method does not modify the current instance.
     */
    public function getCopyWithRandomlyResovledTies() : Ballot
    {
        $candidatesWithTiesBroken = $this->getCandidatesWithTiesRandomlyBroken();
        $candidatesWrappedInLists = $this->wrapEachInCandidateList($candidatesWithTiesBroken);
        $copy = new NBallot($this->count, ...$candidatesWrappedInLists);
        return $copy;
    }
}
