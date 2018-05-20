<?php


namespace PivotLibre\Tideman;

class WinningVoteCalculator extends PairCalculator
{

    /**
     *
     * This class...
     * ...increments the A->B Pair in the Pair registry if A is preferred over B
     * ...increments the B->A Pair in the Pair registry if B is preferred over A
     * ...increments both the A->B and B->A Pairs in the Pair registry if A and B are tied
     * @inheritdoc
     *
     */
    public function updateRegistry(
        PairRegistry $registry,
        int $comparisonFactor,
        Candidate $candidateA,
        Candidate $candidateB,
        int $ballotCount
    ) : void {

        if (1 === $comparisonFactor || 0 == $comparisonFactor) {
            $this->incrementPairInRegistry(
                $candidateA,
                $candidateB,
                $registry,
                $ballotCount
            );
        }
        if (-1 === $comparisonFactor || 0 == $comparisonFactor) {
            $this->incrementPairInRegistry(
                $candidateB,
                $candidateA,
                $registry,
                $ballotCount
            );
        }
    }
}
