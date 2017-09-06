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
use PivotLibre\Tideman\ListOfMarginLists;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreakingMarginComparator;

class RankedPairsCalculator
{
    private $tieBreakingMarginComparator;
    private $tieBreakingCandidateComparator;
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
            $this->tieBreakingCandidateComparator = new CandidateComparator($myTieBreakingBallot);
        }
    }

    /**
     * @param int number of winners to return.
     * @param NBallot the ballots from the electorate.
     * @return CandidateList in which the zeroth Candidate is the most preferred, the first Candidate is the second most
     * preferred, and so on until the last Candidate who is the least preferred. Ties are broken according to the Ballot
     * provided to the constructor.
     */
    public function calculate(int $numWinners, NBallot ...$nBallots) : CandidateList
    {
        $agenda = new Agenda(...$nBallots);
        $candidatesInOrder = [];
        while (sizeof($candidatesInOrder) < $numWinners) {
            $winnersOfTheRound = $this->getOneRoundOfWinners($agenda, ...$nBallots)->toArray();
            array_push($candidatesInOrder, ...$winnersOfTheRound);
            $agenda->removeCandidates(...$winnersOfTheRound);
        }
        $winners = new CandidateList(...$candidatesInOrder);
        return $winners;
    }

    /**
     * @param Agenda of Candidates to consider in this round. Not all Candidates on the Ballots need be in the Agenda..
     * Successive rounds of determining winners will have a smaller and smaller Agenda as fewer candidates
     * are elegible to be winners.
     * @param ... NBallot the ballots submitted to decide the election.
     * @return CandidateList, usually of length one, but possibly greater if the result was a tie. Ties are broken
     * according to the Ballot provided to the constructor.
     */
    public function getOneRoundOfWinners(Agenda $agenda, NBallot ...$nBallots) : CandidateList
    {
        $marginList = $this->getMargins($agenda, ...$nBallots);
        $sortedMarginList = $this->sortMargins($marginList);
        $rankedPairsGraph = new RankedPairsGraph();
        $rankedPairsGraph->addMargins($sortedMarginList);
        $winnersOfTheRound = $rankedPairsGraph->getWinningCandidates();
        //winners may contain ties. Ensure that they are sorted according to our tie-breaking ballot.
        $winnersOfTheRound->sort($this->tieBreakingCandidateComparator);
        return $winnersOfTheRound;
    }

    /**
     * Tallies and returns the Margins
     */
    public function getMargins(Agenda $agenda, NBallot ...$nBallots) : MarginList
    {
        $marginCalculator = new MarginCalculator();
        $marginRegistry = $marginCalculator->calculate($agenda, ...$nBallots);
        $allMargins = $marginRegistry->getAll();
        return $allMargins;
    }

    /**
     * Sorts all Margins in order of descending getDifference(). Breaks ties. Filters out redundant negative margins.
     */
    public function sortMargins(MarginList $marginList) : MarginList
    {
        $marginsSortedDescGroupedByDifference = $marginList->filterGroupAndSort();
        $marginsWithTiesBroken = $marginsSortedDescGroupedByDifference->breakTies($this->tieBreakingMarginComparator);
        return $marginsWithTiesBroken;
    }
}
