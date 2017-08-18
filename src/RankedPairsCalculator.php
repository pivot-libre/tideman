<?php

namespace PivotLibre\Tideman;

class RankedPairsCalculator
{

    /**
     * @return CandidateList in which the zeroth Candidate is the most preferred, the first Candidate is the second most
     * preferred, and so on until the last Candidate who is the least preferred.
     */
    public function calculate(NBallot ...$nBallots) : CandidateList
    {
        $marginCalculator = new MarginCalculator();
        $marginRegistry = $marginCalculator->calculate(...$nBallots);
        $allMargins = $marginRegistry->getAll();
        $marginGroupsSortedDescByDifference = $allMargins->filterGroupAndSort();
        $marginsWithoutTies = $this->breakTies($marginGroupsSortedDescByDifference);
    }

    /**
     * @param List of Margin lists. If a Margin List is of nonzero length, the
     * @return List of Margins
     */
    public function breakTies($listOfMarginLists) : MarginList
    {
    }
}
