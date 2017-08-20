<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\ListOfMarginLists;

/**
 * A MarginTieBreaker specifies some tie-breaking rule that determines which Margin should be considered the winner
 * over another Margin even though their difference properties are identical.
 */
interface MarginTieBreaker
{

    /**
     * If Margin $a should be considered to be more preferred than $b, return TRUE. Otherwise, if Margin $b should be
     * considered to be more preferred than $a, return FALSE. If the Margins' difference properties are not equal, then
     * throw an InvalidArgumentException.
     */
    public function breakTie(Margin $a, Margin $b) : bool;

    /**
     * Given a ListOfMarginLists, produce a MarginList, ordering the Margins such that:
     * A) The Margins' difference properties decrease monotonically from left (low numeric index) to right (high
     *    numeric index)
     * B) Margins with identical difference properties are ordered such that the Margins that won the tie breaking
     *    appear at a lower index in the list than the Margin that lost the tie breaking.
     *
     */
    // public function breakTies(ListOfMarginLists $ListOfMarginLists) : MarginList;
}
