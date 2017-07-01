<?php
namespace PivotLibre\Tideman;

class Ballot extends GenericCollection
{
    public function __construct(CandidateList ...$listsOfCandidates)
    {
        $this->values = $listsOfCandidates;
    }
}
