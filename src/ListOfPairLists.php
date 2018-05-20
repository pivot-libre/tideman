<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\TieBreaking\TieBreakingPairComparator;

class ListOfPairLists extends GenericCollection
{
    public function __construct(PairList ...$pairLists)
    {
        $this->values = $pairLists;
    }

    /**
     * Flattens all of this instances' many PairLists into a single PairList.
     * Assumes that Pairs that initially share a nested PairList are tied.
     * Breaks the ties using the parameterized TieBreakingPairComparator.
     * In other words, the order in which Pairs from one of the nested PairLists are placed into the flattened
     * PairList is determined by the parameterized TieBreakingPairComparator.
     */
    public function breakTies(TieBreakingPairComparator $tieBreakingPairComparator) : PairList
    {
        $initial = [];
        $result = array_reduce(
            $this->values,
            function (array $carry, PairList $current) use ($tieBreakingPairComparator) {
                $currentValues = $current->toArray();
                usort($currentValues, $tieBreakingPairComparator);
                array_push($carry, ...$currentValues);
                return $carry;
            },
            $initial
        );
        $resultPairList = new PairList(...$result);
        return $resultPairList;
    }
}
