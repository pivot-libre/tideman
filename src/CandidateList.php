<?php
namespace PivotLibre\Tideman;

class CandidateList extends GenericCollection
{
    public function __construct(Candidate /*...*/$candidates)
    {
        $this->values = $candidates;
    }
}
