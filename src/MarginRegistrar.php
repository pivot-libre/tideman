<?php
namespace PivotLibre\Tideman;

/**
 * Class MarginRegistrar tallies the votes in a "margins" fashion.
 * @package PivotLibre\Tideman
 */
class MarginRegistrar extends PairRegistrar
{
    /**
     *
     * This method...
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
        //since margins record the difference, we also need to decrement the transposed pair
        $this->incrementVotesInRegistry(
            $candidateB,
            $candidateA,
            $registry,
            -1 * $comparisonFactor * $ballotCount
        );
    }
}
