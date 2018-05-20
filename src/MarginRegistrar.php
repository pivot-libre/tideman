<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

use \InvalidArgumentException;

class MarginRegistrar extends PairRegistrar
{
    /**
     *
     * This class...
     * ...increments the A->B Pair and decrements the B->A Pair if A is preferred over B
     * ...increments the B->A Pair and decrements the A->B Pair if B is preferred over A
     * ...changes the votes on neither A->B and B->A Pairs in the Pair registry a tie
     * ...does not modify the $indifference property of Pairs.
     * @inheritdoc
     */
    public function updateRegistry(
        PairRegistry $registry,
        int $comparisonFactor,
        Candidate $candidateA,
        Candidate $candidateB,
        int $ballotCount
    ) : void {
        $this->incrementVotesInRegistry(
            $candidateA,
            $candidateB,
            $registry,
            $comparisonFactor * $ballotCount
        );
        //since margins record the difference, we also need to decrement the pair representing the mirror image
        $this->incrementVotesInRegistry(
            $candidateB,
            $candidateA,
            $registry,
            -1 * $comparisonFactor * $ballotCount
        );
    }
}
