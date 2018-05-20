<?php


namespace PivotLibre\Tideman;


class WinningVoteCalculator extends PairCalculator
{

    public function updateRegistry(PairRegistry $registry, Candidate $winner, Candidate $loser, int $ballotCount) : void
    {
        $this->incrementPairInRegistry(
            $winner,
            $loser,
            $registry,
            $ballotCount
        );
    }
}