<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

/**
 * This Candidate Comparator considers Candidates that it doesn't know to be in last place.
 */
class TolerantCandidateComparator extends CandidateComparator
{
    protected function getRank(Candidate $candidate) : int
    {
        $id = $candidate->getId();
        if (!isset($this->candidateIdToRank[$id])) {
            //set rank to last place
            $rank = $this->ballot->count();
        } else {
            $rank = $this->candidateIdToRank[$id];
        }
        return $rank;
    }
}
