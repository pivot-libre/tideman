<?php


namespace PivotLibre\Tideman;

/**
 * Class Result
 * Instances of this class describe the final result of the election (@see Result::getRanking()) and a few other
 * intermediate results for reporting and visualization purposes.
 *
 * @package PivotLibre\Tideman
 */
class Result
{
    protected $ranking;
    protected $winningVotesTally;
    protected $marginsTally;
    protected $tieBreakingBallot;


    /**
     * Get the final results of the election.
     * @return CandidateRanking
     */
    public function getRanking() : CandidateRanking
    {
        return $this->ranking;
    }

    /**
     * @return PairRegistry
     * @see WinningVoteRegistrar::updateRegistry() for the meaning of the numeric values on each Pair.
     */
    public function getWinningVotesTally() : PairRegistry
    {
        return $this->winningVotesTally;
    }

    /**
     * @return PairRegistry
     * @see MarginRegistrar::updateRegistry() for the meaning of the numeric values on each Pair.
     */
    public function getMarginsTally() : PairRegistry
    {
        return $this->marginsTally;
    }

    /**
     * @return Ballot
     */
    public function getTieBreakingBallot() : Ballot
    {
        return $this->tieBreakingBallot;
    }

    /**
     * @param CandidateRanking $ranking
     * @return $this
     */
    public function setRanking(CandidateRanking $ranking)
    {
        $this->ranking = $ranking;
        return $this;
    }

    /**
     * @param PairRegistry $winningVotesTally
     * @return Result
     */
    public function setWinningVotesTally(PairRegistry $winningVotesTally)
    {
        $this->winningVotesTally = $winningVotesTally;
        return $this;
    }

    /**
     * @param PairRegistry $marginsTally
     * @return Result
     */
    public function setMarginsTally(PairRegistry $marginsTally)
    {
        $this->marginsTally = $marginsTally;
        return $this;
    }

    /**
     * @param Ballot $tieBreakingBallot
     * @return Result
     */
    public function setTieBreakingBallot(Ballot $tieBreakingBallot)
    {
        $this->tieBreakingBallot = $tieBreakingBallot;
        return $this;
    }
}
