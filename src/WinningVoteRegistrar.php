<?php


namespace PivotLibre\Tideman;

/**
 * Class WinningVoteRegistrar tallies the votes in a "winning votes" fashion.
 * @package PivotLibre\Tideman
 */
class WinningVoteRegistrar extends PairRegistrar
{

    /**
     *
     * Adds the $amountToAdd to the indifference already associated with the appropriate Pair in the
     * PairRegistry.
     *
     * @param Candidate $winner
     * @param Candidate $loser
     * @param PairRegistry $registry
     * @param int $amountToAdd
     */
    public function incrementIndifferenceInRegistry(
        Candidate $winner,
        Candidate $loser,
        PairRegistry $registry,
        int $amountToAdd
    ) : void {
        $pairToUpdate = $registry->get($winner, $loser);
        $updatedIndifference = $pairToUpdate->getIndifference() + $amountToAdd;
        $pairToUpdate->setIndifference($updatedIndifference);
    }

    /**
     *
     * This class...
     * ...increments the votes of the A->B Pair in the Pair registry if A is preferred over B
     * ...increments the votes of the B->A Pair in the Pair registry if B is preferred over A
     * ...increments the votes of both the A->B and B->A Pairs in the Pair registry if A and B are tied
     * ...increments the indifference of both the A->B and B->A Pairs in the Pair registry if A and B are tied
     *
     * This handling of ties is functionally consistent with Ron McKinnon's interpretation that ties should be added
     * to both the majority and the minority vote, as long as users understand that `p->getVotes()` for a Pair `p` is
     * the sum of (the votes that preferred the winner over the loser) and (the votes that considered the winner and
     * loser to be equivalent). In other words, a Pair `p`'s `p.getVotes()` ALREADY CONTAINS contributions from
     * indifferent voters. To get a Pair `p`'s number of winning votes without contributions from indifferent voters,
     * users should use `p->getVotes() - p->getIndifference()`.
     *
     * Details: http://condorcet.ca/see-how-it-works/how-it-works/
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
            $this->incrementVotesInRegistry(
                $candidateA,
                $candidateB,
                $registry,
                $ballotCount
            );
        }
        if (-1 === $comparisonFactor || 0 == $comparisonFactor) {
            $this->incrementVotesInRegistry(
                $candidateB,
                $candidateA,
                $registry,
                $ballotCount
            );
        }
        if (0 === $comparisonFactor) {
            $this->incrementIndifferenceInRegistry($candidateA, $candidateB, $registry, $ballotCount);
            $this->incrementIndifferenceInRegistry($candidateB, $candidateA, $registry, $ballotCount);
        }
    }
}
