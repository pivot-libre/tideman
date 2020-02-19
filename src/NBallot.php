<?php
namespace PivotLibre\Tideman;

/**
 * This class shows how many times the same Ballot was submitted in an election.
 * This object's `multiplier` could be populated by the result of a count SQL query like:
 * SELECT count(ranking), ranking FROM ballots WHERE ranking = ...
 */
class NBallot extends CandidateRanking
{
    private $multiplier;
    public function __construct(int $multiplier, CandidateList ...$listsOfCandidates)
    {
        parent::__construct(...$listsOfCandidates);
        $this->multiplier = $multiplier;
    }
    public function getMultiplier()
    {
        return $this->multiplier;
    }
}
