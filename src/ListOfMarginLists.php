<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\TieBreaking\TieBreakingMarginComparator;

class ListOfMarginLists extends GenericCollection
{
    public function __construct(MarginList ...$marginLists)
    {
        $this->values = $marginLists;
    }

    /**
     * Flattens all of this instances' many MarginLists into a single MarginList.
     * Assumes that Margins that initially share a nested MarginList are tied.
     * Breaks the ties using the parameterized TieBreakingMarginComparator.
     * In other words, the order in which Margins from one of the nested MarginLists are placed into the flattened
     * MarginList is determined by the parameterized TieBreakingMarginComparator.
     */
    public function breakTies(TieBreakingMarginComparator $tieBreakingMarginComparator) : MarginList
    {
        $initial = [];
        $result = array_reduce(
            $this->values,
            function (array $carry, MarginList $current) use ($tieBreakingMarginComparator) {
                $currentValues = $current->toArray();
                usort($currentValues, $tieBreakingMarginComparator);
                array_push($carry, ...$currentValues);
                return $carry;
            },
            $initial
        );
        $resultMarginList = new MarginList(...$result);
        return $resultMarginList;
    }
}
