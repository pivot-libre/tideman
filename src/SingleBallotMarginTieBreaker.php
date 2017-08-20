<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginTieBreaker;

abstract class SingleBallotMarginTieBreaker implements MarginTieBreaker
{
    //TBRC = tie-breaking ranking of Candidates
    protected $tbrc;
    public function __construct(Ballot $ballot)
    {
        $this->tbrc = $ballot;
    }
}
