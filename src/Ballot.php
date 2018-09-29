<?php
namespace PivotLibre\Tideman;

class Ballot extends NBallot
{
    public function __construct(CandidateList ...$candidateLists)
    {
        parent::__construct(1, ...$candidateLists);
    }
}
