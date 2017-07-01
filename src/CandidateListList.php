<?php
namespace PivotLibre\Tideman;

/**
 * This class stores a list of lists of candidates.
 */
class CandidateListList extends GenericCollection
{
    public function __construct(CandidateList /*...*/$listsOfCandidates)
    {
        $this->values = $listsOfCandidates;
    }
}
