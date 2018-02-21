<?php
namespace PivotLibre\Tideman;

use \Countable;
use PivotLibre\Tideman\ListOfMarginLists;

class MarginList extends GenericCollection implements Countable
{
    private $grouper;

    public function __construct(Margin ...$margins)
    {
        $this->values = $margins;
        $this->grouper = new Grouper(function (Margin $margin) {
            return $margin->getDifference();
        });
    }

    /**
     * This method returns the result of filtering, grouping, and sorting this MarginList's Margins.
     * This method filters out Margins with negative difference values.
     * This method sorts the MarginList in order of descending difference properties.
     * This method groups this MarginList's Margins into multiple MarginLists based on Margins' difference properties.
     *
     *
     * For example, if this MarginList were comprised of:
     * [ $marginW, $marginZ, $marginY, $marginX ]
     *
     * Where:
     * $marginW = (B --(-10)-->A) //a negative-valued Margin
     * $marginX = (A --(10)--> B)
     * $marginY = (B --(10)--> C)
     * $marginZ = (C --(5)--> B)
     *
     * Then the ListOfMarginLists returned by groupAndSort() would be:
     * [ [$marginX, $marginY], [$marginZ] ]
     *
     * Here is the same result in expanded form:
     * [ [ (A --(10)--> B),  (B --(10)--> C) ], [ (C --(5)--> B) ] ]
     *
     * Explanation:
     * $marginX and $marginY will be placed in the same MarginList of length two because both $marginX and $marginY
     * have the same getDifference() value. Additionally, $marginZ will be placed in its own MarginList of length one
     * because no other Margin in the original MarginList has the same getDifference() value. The MarginList of length
     * two that contains $marginX and $marginY would be sorted to a lower index in the resulting ListOfMarginLists than
     * the MarginList of length one that contains $marginZ because ten (the getDifference() of both $marginX and
     * $marginY) is greater than five (the getDifference() of $marginZ). $marginW is excluded from the output because it
     * has a negative difference value, and negative difference values are redundant.
     */
    public function filterGroupAndSort() : ListOfMarginLists
    {
        $marginArray = $this->toArray();
        $positiveMargins = array_filter($marginArray, function (Margin $margin) {
            return $margin->getDifference() >= 0;
        });
        $marginsGroupedByDifference = $this->grouper->group($positiveMargins);
        ksort($marginsGroupedByDifference, SORT_NUMERIC);
        //we want DESCENDING order
        $marginsSortedDescGroupedByDifference = array_reverse($marginsGroupedByDifference);
        $allMarginLists = [];
        foreach ($marginsSortedDescGroupedByDifference as $difference => $group) {
            $marginList = new MarginList(...$group);
            $allMarginLists[] = $marginList;
        }
        $listOfMarginLists = new ListOfMarginLists(...$allMarginLists);
        return $listOfMarginLists;
    }

    public function count() : int
    {
        return sizeof($this->values);
    }
}
