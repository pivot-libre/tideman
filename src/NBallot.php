<?php
namespace PivotLibre\Tideman;

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
}
