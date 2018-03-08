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
use PivotLibre\Tideman\TieBreaking\TieBreakingMarginComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotMarginTieBreaker;
use PivotLibre\Tideman\TieBreaking\BallotTieBreaker;

class RankedPairsCalculator
{
    private $tieBreakingMarginComparator;
    private $tieBreakingCandidateComparator;

    /**
     * Constructs a Ranked Pairs Calculator. If the parameterized tie-breaking Ballot contains ties,
     * they will be resolved randomly. This instance retains a copy of the tie-breaking Ballot so that
     * the caller may modify the parameterized Ballot without affecting this instance.
     * @param tieBreakingBallot
     */
    public function __construct(Ballot $tieBreakingBallot)
    {
        //make a copy so that the caller could modify it safely
        $myTieBreakingBallot = clone $tieBreakingBallot;
        if ($myTieBreakingBallot->containsTies()) {
            $ballotTieBreaker = new BallotTieBreaker();
            $myTieBreakingBallot = $ballotTieBreaker->breakTiesRandomly($myTieBreakingBallot);
        }
        $tieBreaker = new TotallyOrderedBallotMarginTieBreaker(new CandidateComparator($myTieBreakingBallot));
        $this->tieBreakingMarginComparator = new TieBreakingMarginComparator($tieBreaker);
        $this->tieBreakingCandidateComparator = new CandidateComparator($myTieBreakingBallot);
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
            $winnersOfTheRound = $this->getOneRoundOfWinners($agenda, ...$nBallots);
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
        // if only one Candidate remains, return that Candidate.
        if (1 === $agenda->count()) {
            return $agenda->getCandidates();
        } else {
            $marginList = $this->getMargins($agenda, ...$nBallots);
            $sortedMarginList = $this->sortMargins($marginList);
            $rankedPairsGraph = new RankedPairsGraph();
            $rankedPairsGraph->addMargins($sortedMarginList);
            $winnersOfTheRound = $rankedPairsGraph->getWinningCandidates();
            //winners may contain ties. Ensure that they are sorted according to our tie-breaking ballot.
            $winnersOfTheRound->sort($this->tieBreakingCandidateComparator);
            return $winnersOfTheRound;
        }
    }

    /**
     * Calculate the pairwise differences in popular support, a.k.a. the Margins.
     *
     * @param Agenda $agenda a set of candidates. This is a non-strict subset of the Candidates in $nBallots.
     * @param ...NBallot $nBallots a list of NBallots. The set of Candidates in $nBallots is a non-strict
     * superset of the Candidates in $agenda.
     * @return MarginList representing all of the pairwise differences in popular support for all Candidates specified
     * by $agenda. The length of the returned MarginList should be equal to `N(N - 1)`, where `N` is the number of
     * Candidates in $agenda.
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
