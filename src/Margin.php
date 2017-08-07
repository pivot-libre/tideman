<?php
namespace PivotLibre\Tideman;

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
    public function __toString() : string
    {
        return $this->winner->getId() . " --" . $this->difference . "--> " . $this->loser->getId();
    }
}
