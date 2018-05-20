<?php
namespace PivotLibre\Tideman;

/**
 * A Pair compares the popular support between two Candidates.
 */
class Pair
{
    private $winner;
    private $loser;
    private $votes;

    public function __construct(Candidate $winner, Candidate $loser, int $votes)
    {
        $this->winner = $winner;
        $this->loser = $loser;
        $this->votes = $votes;
    }

    public function getWinner() : Candidate
    {
        return $this->winner;
    }

    public function getLoser() : Candidate
    {
        return $this->loser;
    }

    public function getVotes() : int
    {
        return $this->votes;
    }
    public function setVotes($votes) : void
    {
        $this->votes = $votes;
    }
    /**
     * Represent the Pair by placing the winning candidate's ID on the left, followed by an arrow pointing to the
     * ID of the losing candidate on the right. The arrow is interrupted with a parenthesized number of votes
     * For example, to represent a Pair with a winning Candidate A, a losing Candidate B, and 10 votes:
     * (A--(10)-->B)
     * To represent the inverse of the same relationship:
     * (B--(-10)-->A)
     */
    public function __toString() : string
    {
        return "(" . $this->winner->getId() . " --(" . $this->votes . ")--> " . $this->loser->getId() . ")";
    }
}
