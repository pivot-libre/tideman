<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

/**
 * This Candidate Comparator raises an Exception if asked to compare candidates that it doesn't know about.
 */
class StrictCandidateComparator extends CandidateComparator
{
    protected function getRank(Candidate $candidate) : int
    {
        $id = $candidate->getId();
        if (!isset($this->candidateIdToRank[$id])) {
            throw new InvalidArgumentException(
                "Candidate's Id should be in the map of ID to Rank.\n"
                . " Candidate: " . $id . "\n"
                . " Mapping: " . var_export($this->candidateIdToRank, true)
            );
        }
        $rank = $this->candidateIdToRank[$id];
        return $rank;
    }
}
