<?php
namespace PivotLibre\Tideman;

use \Countable;
use PivotLibre\Tideman\ListOfPairLists;

class PairList extends GenericCollection implements Countable
{
    private $grouper;

    public function __construct(Pair ...$pairs)
    {
        $this->values = $pairs;
        $this->grouper = new Grouper(function (Pair $pair) {
            return $pair->getVotes();
        });
    }

    /**
     * This method groups and sorts this PairList's Pairs based on the Pairs' `getVotes()` values.
     * This method groups Pairs with the same `getVotes()` values into the same PairList.
     * This method sorts the PairLists in order of descending `getVotes()`.
     *
     *
     * For example, if this PairList were comprised of:
     * [ $pairZ, $pairY, $pairX ]
     *
     * Where:
     * $pairX = (A --(10)--> B)
     * $pairY = (B --(10)--> C)
     * $pairZ = (C --(5)--> B)
     *
     * Then the ListOfPairLists returned by groupAndSort() would be:
     * [ [$pairX, $pairY], [$pairZ] ]
     *
     * Here is the same result in expanded form:
     * [ [ (A --(10)--> B),  (B --(10)--> C) ], [ (C --(5)--> B) ] ]
     *
     * Explanation:
     * $pairX and $pairY will be placed in the same PairList of length two because both $pairX and $pairY
     * have the same getVotes() value. Additionally, $pairZ will be placed in its own PairList of length one
     * because no other Pair in the original PairList has the same getVotes() value. The PairList of length
     * two that contains $pairX and $pairY would be sorted to a lower index in the resulting ListOfPairLists than
     * the PairList of length one that contains $pairZ because ten (the getVotes() of both $pairX and
     * $pairY) is greater than five (the getVotes() of $pairZ).
     */
    public function groupAndSort() : ListOfPairLists
    {
        $pairsGroupedByVotes = $this->grouper->group($this);
        ksort($pairsGroupedByVotes, SORT_NUMERIC);
        //we want DESCENDING order
        $pairsSortedDescGroupedByVotes = array_reverse($pairsGroupedByVotes);
        $allPairLists = [];
        foreach ($pairsSortedDescGroupedByVotes as $votes => $group) {
            $pairList = new PairList(...$group);
            $allPairLists[] = $pairList;
        }
        $listOfPairLists = new ListOfPairLists(...$allPairLists);
        return $listOfPairLists;
    }
}
