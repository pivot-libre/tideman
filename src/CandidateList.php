<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\CandidateComparator;

class CandidateList extends GenericCollection
{
    public function __construct(Candidate ...$candidates)
    {
        $this->values = $candidates;
    }
    /**
     * Re-orders the Candidates in this CandidateList list according to the parameterized CandidateComparator
     */
    public function sort(CandidateComparator $comparator) : void
    {
        usort($this->values, $comparator);
    }
}
