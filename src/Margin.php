<?php
namespace PivotLibre\Tideman;

/**
 * A Margin describes the difference in popular support between two Candidates.
 */
class Margin
{
    private $winner;
    private $loser;
    private $difference;

    public function __construct(Candidate $winner, Candidate $loser, int $difference)
    {
        $this->winner = $winner;
        $this->loser = $loser;
        $this->difference = $difference;
    }

    public function getWinner() : Candidate
    {
        return $this->winner;
    }

    public function getLoser() : Candidate
    {
        return $this->loser;
    }

    public function getDifference() : int
    {
        return $this->difference;
    }
    public function setDifference($difference) : void
    {
        $this->difference = $difference;
    }
    /**
     * Represent the Margin by placing the winning candidate's ID on the left, followed by an arrow pointing to the
     * ID of the losing candidate on the right. The arrow is interrupted with a parenthesized number that is the
     * difference in popular support betweene the winner and the loser.
     * For example, to represent a Margin with a winning Candidate A, a losing Candidate B, and a difference of 10:
     * (A--(10)-->B)
     * To represent the inverse of the same releationship:
     * (B--(-10)-->A)
     */
    public function __toString() : string
    {
        return "(" . $this->winner->getId() . " --(" . $this->difference . ")--> " . $this->loser->getId() . ")";
    }
}
