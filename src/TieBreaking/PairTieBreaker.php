<?php
namespace PivotLibre\Tideman\TieBreaking;

use PivotLibre\Tideman\ListOfPairLists;
use PivotLibre\Tideman\Pair;

/**
 * A PairTieBreaker specifies some tie-breaking rule that determines which Pair should be considered the winner
 * over another Pair even though their votes properties are identical.
 */
interface PairTieBreaker
{

    /**
     * @return int If Pair $a should be considered to be more preferred than $b, return an integer greater than 0.
     * If Pair $b should be considered to be more preferred than $a, return an integer less than 0. If the Pairs'
     * votes properties are not equal, then throw an InvalidArgumentException.
     * Implementations of this method should never return zero, because a zero would imply a tie.
     */
    public function breakTie(Pair $a, Pair $b) : int;
}
