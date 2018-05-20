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
     * This method returns the result of filtering, grouping, and sorting this PairList's Pairs.
     * This method filters out Pairs with negative "votes" values.
     * This method sorts the PairList in order of descending "votes" properties.
     * This method groups this PairList's Pairs into multiple PairLists based on Pairs' "votes" properties.
     *
     *
     * For example, if this PairList were comprised of:
     * [ $pairW, $pairZ, $pairY, $pairX ]
     *
     * Where:
     * $pairW = (B --(-10)-->A) //a negative-valued Pair
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
     * $pairY) is greater than five (the getVotes() of $pairZ). $pairW is excluded from the output because it
     * has a negative "votes" value, and negative "votes" values are redundant.
     */
    public function filterGroupAndSort() : ListOfPairLists
    {
        $pairArray = $this->toArray();
        $positivePairs = array_filter($pairArray, function (Pair $pair) {
            return $pair->getVotes() >= 0;
        });
        $pairsGroupedByVotes = $this->grouper->group($positivePairs);
        ksort($pairsGroupedByVotes, SORT_NUMERIC);
        //we want DESCENDING order
        $pairsSortedDescGroupedByVotes = array_reverse($pairsGroupedByVotes);
        $allPairLists = [];
        foreach ($pairsSortedDescGroupedByVotes as $difference => $group) {
            $PairList = new PairList(...$group);
            $allPairLists[] = $PairList;
        }
        $listOfPairLists = new ListOfPairLists(...$allPairLists);
        return $listOfPairLists;
    }
}
