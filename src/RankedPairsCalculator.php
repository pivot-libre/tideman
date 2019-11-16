<?php

namespace PivotLibre\Tideman;

use \InvalidArgumentException;
use \Exception;

use PivotLibre\Tideman\Agenda;
use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\PairList;
use PivotLibre\Tideman\CandidateSet;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\RankedPairsGraph;
use PivotLibre\Tideman\ListOfPairLists;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreaking\TieBreakingPairComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotPairTieBreaker;
use PivotLibre\Tideman\TieBreaking\BallotTieBreaker;

class RankedPairsCalculator
{
    private $tieBreakingBallot;

    private $tieBreakingPairComparator;
    private $tieBreakingCandidateComparator;

    private $marginRegistrar;
    private $winningVotesRegistrar;

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
        $this->tieBreakingBallot = $myTieBreakingBallot;

        $tieBreaker = new TotallyOrderedBallotPairTieBreaker(new CandidateComparator($myTieBreakingBallot));
        $this->tieBreakingPairComparator = new TieBreakingPairComparator($tieBreaker);
        $this->tieBreakingCandidateComparator = new CandidateComparator($myTieBreakingBallot);

        $this->marginRegistrar = new MarginRegistrar();
        $this->winningVotesRegistrar = new WinningVoteRegistrar();
    }

    /**
     * @param int number of winners to return. Specify a negative number to determine the rank for all candidates
     * @param Agenda|null $agenda optional agenda. If not provided, the agenda will be derived from the NBallots
     * @param NBallot[] $nBallots
     * @return Result
     */
    public function calculate(int $numWinners, Agenda $agenda = null, NBallot ...$nBallots) : Result
    {
        $agenda = $agenda ?: new Agenda(...$nBallots);
        $numWinners = 0 > $numWinners ? count($agenda) : $numWinners;
        $marginRegistry = $this->marginRegistrar->register($agenda, ...$nBallots);
        $winningVotesRegistry = $this->winningVotesRegistrar->register($agenda, ...$nBallots);

        $sortedPairList = $this->sortPairs($marginRegistry);

        $rankedPairsGraph = new RankedPairsGraph();
        $rankedPairsGraph->addCandidatesFromAgenda($agenda);
        $rankedPairsGraph->addPairs($sortedPairList);

        //Build an array of CandidateLists.
        //The first appended is the most preferred, the second the second-most preferred etc.
        $rankedCandidateLists = [];

        for ($winCounter = 0; $winCounter < $numWinners && !$rankedPairsGraph->isEmpty(); $winCounter++) {
            $winnersOfTheRound = $rankedPairsGraph->getWinningCandidates();
            $rankedCandidateLists[] = $winnersOfTheRound;
            $rankedPairsGraph->removeCandidates(...$winnersOfTheRound);
        }

        $ranking = new CandidateRanking(...$rankedCandidateLists);

        $result = new Result();
        $result->setRanking($ranking)
            ->setMarginsTally($marginRegistry)
            ->setWinningVotesTally($winningVotesRegistry)
            ->setTieBreakingBallot($this->tieBreakingBallot);

        return $result;
    }


    /**
     * Sorts all Pairs in order of descending "getVotes()". Breaks ties between Pairs with equal `getVotes()`.
     * @param PairRegistry $pairRegistry
     * @return PairList
     *
     */
    public function sortPairs(PairRegistry $pairRegistry) : PairList
    {
        $dominatingPairs = $pairRegistry->getDominatingPairs();
        $pairsSortedDescGroupedByVotes = $dominatingPairs->groupAndSort();
        $pairsWithTiesBroken = $pairsSortedDescGroupedByVotes->breakTies($this->tieBreakingPairComparator);
        return $pairsWithTiesBroken;
    }
}
