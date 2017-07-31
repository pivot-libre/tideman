<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class MarginRegistry
{
    private $registry = array();

    protected function makeKey(Candidate $winner, Candidate $loser) : string
    {
        $winnerId = $winner->getId();
        $loserId = $loser->getId();
        $key = $winnerId . $loserId;
        return $key;
    }
    public function register(Margin $margin) : void
    {
        $key =  $this->makeKey($margin->getWinner(), $margin->getLoser());
        if (array_key_exists($key, $this->registry)) {
            throw new InvalidArgumentException(
                "This Margin has already been registered."
            );
        }
        $this->registry[$key] = $margin;
    }
    public function get(Candidate $winner, Candidate $loser) : Margin
    {
        $key = $this->makeKey($winner, $loser);
        if (!array_key_exists($key, $this->registry)) {
            throw new InvalidArgumentException(
                "No margin found for the given pair of Candidates."
            );
        } else {
            $margin = $this->registry[$key];
            return $margin;
        }
    }
    public function getCount() : int
    {
        return sizeof($this->registry);
    }
}
