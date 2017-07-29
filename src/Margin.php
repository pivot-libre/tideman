<?php
namespace PivotLibre\Tideman;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Margin implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
    public function setMargin($margin) : void
    {
        $this->margin = $margin;
    }
    public function __toString() : string
    {
        return $this->winner->getId() . " --" . $this->margin . "--> " . $this->loser->getId();
    }
}
