<?php

namespace PivotLibre\Tideman;

use \InvalidArgumentException;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreakingMarginComparator;

class RankedPairsCalculator
{
    private $tieBreakingBallot;

    /**
     * Constructs a Ranked Pairs Calculator, verifying that the specified tie-breaking ballot contains no ties.
     * @param tieBreakingBallot
     */
    public function __construct(Ballot $tieBreakingBallot)
    {
        if ($tieBreakingBallot->containsTies()) {
            throw new InvalidArgumentException("Tie breaking ballot must not contain any ties. $tieBreakingBallot");
        } else {
            $this->tieBreakingBallot = $tieBreakingBallot;
        }
    }

    /**
     * @return CandidateList in which the zeroth Candidate is the most preferred, the first Candidate is the second most
     * preferred, and so on until the last Candidate who is the least preferred.
     */
    public function calculate(NBallot ...$nBallots) : CandidateList
    {
        $marginCalculator = new MarginCalculator();
        $marginRegistry = $marginCalculator->calculate(...$nBallots);
        $allMargins = $marginRegistry->getAll();
        // $marginGroupsSortedDescByDifference = $allMargins->filterGroupAndSort()
        // $marginsWithoutTies = $this->breakTies($marginGroupsSortedDescByDifference);
        $positiveOrZeroMargins = array_filter($allMargins->toArray(), function (Margin $margin) {
            return $margin->getDifference() >= 0;
        });

        $tieBreaker = new TotallyOrderedBallotMarginTieBreaker(new CandidateComparator($this->tieBreakingBallot));
        $tieBreakingMarginComparator = new TieBreakingMarginComparator($tieBreaker);
        $sortedMargins = usort($positiveOrZeroMargins, $tieBreakingMarginComparator);
    }
}
