<?php
namespace PivotLibre\Tideman\TieBreaking;

use PivotLibre\Tideman\ListOfMarginLists;
use PivotLibre\Tideman\Margin;

/**
 * A MarginTieBreaker specifies some tie-breaking rule that determines which Margin should be considered the winner
 * over another Margin even though their difference properties are identical.
 */
interface MarginTieBreaker
{

    /**
     * @return int If Margin $a should be considered to be more preferred than $b, return an integer greater than 0.
     * If Margin $b should be considered to be more preferred than $a, return an integer less than 0. If the Margins'
     * difference properties are not equal, then throw an InvalidArgumentException.
     * Implementations of this method should never return zero, because a zero would imply a tie.
     */
    public function breakTie(Margin $a, Margin $b) : int;
}
