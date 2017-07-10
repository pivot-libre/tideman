<?php
namespace PivotLibre\Tideman;

class Margin
{
    private $winner;
    private $loser;
    private $margin;

    public function __construct(Candidate $winner, Candidate $loser, int $margin)
    {
        $this->winner = $winner;
        $this->loser = $loser;
        $this->margin = $margin;
    }

    public function getWinner() : Candidate
    {
        return $this->winner;
    }

    public function getLoser() : Candidate
    {
        return $this->loser;
    }

    public function getMargin() : int
    {
        return $this->margin;
    }

    public function __toString()
    {
        return $this->winner->getId() . " --" . $this->margin . "--> " . $this->loser->getId();
    }
}
