<?php

namespace PivotLibre\Tideman;

use \InvalidArgumentException;
use \Exception;

use PivotLibre\Tideman\Agenda;
use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\MarginList;
use PivotLibre\Tideman\CandidateSet;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\RankedPairsGraph;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreakingMarginComparator;

class RankedPairsCalculator
{
    private $tieBreakingMarginComparator;

    /**
     * Constructs a Ranked Pairs Calculator, verifying that the specified tie-breaking ballot contains no ties.
     * Retains a copy of the tie-breaking Ballot so that the caller may modify the parameterized Ballot without
     * affecting this class.
     * @param tieBreakingBallot
     */
    public function __construct(Ballot $tieBreakingBallot)
    {
        if ($tieBreakingBallot->containsTies()) {
            throw new InvalidArgumentException("Tie breaking ballot must not contain any ties. $tieBreakingBallot");
        } else {
            $myTieBreakingBallot = clone $tieBreakingBallot;
            $tieBreaker = new TotallyOrderedBallotMarginTieBreaker(new CandidateComparator($myTieBreakingBallot));
            $this->tieBreakingMarginComparator = new TieBreakingMarginComparator($tieBreaker);
        }
    }

    /**
     * @param int number of winners to return.
     * @return CandidateList in which the zeroth Candidate is the most preferred, the first Candidate is the second most
     * preferred, and so on until the last Candidate who is the least preferred. Ties are broken according
     */
    public function calculate(int $numWinners, NBallot ...$nBallots) : CandidateList
    {
        $agenda = new Agenda(...$nBallots);
        $candidatesInOrder = [];
        while (sizeof($candidatesInOrder) < $numWinners) {
            $winnersFromThisRound = $this->getOneRoundOfWinners($agenda, ...$nBallots)->toArray();
            array_push($candidatesInOrder, ...$winnersFromThisRound);
            $agenda->removeCandidates(...$winnersFromThisRound);
        }
        $winners = new CandidateList(...$candidatesInOrder);
        return $winners;
    }

    /**
     * @param Agenda of Candidates to consider in this round. Not all Candidates on the Ballots need be considered.
     * Chiefly, successive rounds of determining winners will have a smaller and smaller Agenda as fewer candidates
     * are elegible to be winners.
     * @param ... NBallot the ballots submitted to decide the election.
     * @return CandidateList, usually of length one, but possibly greater if the result was a tie.
     */
    public function getOneRoundOfWinners(Agenda $agenda, NBallot ...$nBallots) : CandidateList
    {
        $marginList = $this->getMargins($agenda, ...$nBallots);
        $sortedMarginList = $this->sortMargins($marginList);
        $rankedPairsGraph = new RankedPairsGraph();
        $rankedPairsGraph->addMargins($sortedMarginList);
        $tiedWinners = $rankedPairsGraph->getWinningCandidates();
        $sortedWinners = $this->breakTies($tiedWinners, $this->tieBreakingMarginComparator);
        return $sortedWinners;
    }

    /**
     * Tallies the Margins and returns the Margins with difference properties >= 0
     */
    public function getMargins(Agenda $agenda, NBallot ...$nBallots) : MarginList
    {
        $marginCalculator = new MarginCalculator();
        $marginRegistry = $marginCalculator->calculate($agenda, ...$nBallots);
        $allMargins = $marginRegistry->getAll();
        $positiveOrZeroMargins = array_filter($allMargins->toArray(), function (Margin $margin) {
            return $margin->getDifference() >= 0;
        });
        $marginList = new MarginList(...$positiveOrZeroMargins);
        return $marginList;
    }

    /**
     * Sorts all Margins in order of descending getDifference().
     */
    public function sortMargins(MarginList $marginList) : MarginList
    {

        $sortedMargins = usort($positiveOrZeroMargins, function (Margin $a, Margin $b) {
            //DESCENDING getDifference requires that $a's value be subtracted from $b's value.
            return $b->getDifference() - $a->getDifference();
        });
        $sortedMarginList = new MarginList(...$sortedMargins);
        return $sortedMargins;
    }

    /**
     * Sort a list of tied candidates according to a TieBreakingMarginComparator
     */
    public function breakTies(
        CandidateList $tiedCandidates,
        TieBreakingMarginComparator $tieBreakingMarginComparator
    ) : CandidateList {
        $tiedCandidatesArray = $tiedCandidates->toArray();
        usort($tiedCandidatesArray, $tieBreakingMarginComparator);
        //give a better name
        $sortedCandidates = $tiedCandidatesArray;
        return $sortedCandidates;
    }
}
