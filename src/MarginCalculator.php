<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

use \InvalidArgumentException;

class MarginCalculator extends PairCalculator
{

    public function updateRegistry(PairRegistry $registry, Candidate $winner, Candidate $loser, int $ballotCount)
    {
        $this->incrementMarginInRegistry(
            $winner,
            $loser,
            $registry,
            $ballotCount
        );
        //since margins record the difference, we also need to decrement the pair representing the mirror image
        $this->incrementMarginInRegistry(
            $loser,
            $winner,
            $registry,
            -1 * $$ballotCount
        );
    }
}
