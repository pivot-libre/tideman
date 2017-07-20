<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class MarginRegistry
{
    private $registry = array();

    protected function getCandidateIds(Candidate $winner, Candidate $loser) : array
    {
        $winnerId = $winner->getId();
        $loserId = $loser->getId();
        if (empty($winnerId)) {
            throw new InvalidArgumentException(
                "The Margin's winner Candidate had no id. No registry key could be formed."
            );
        }
        if (empty($loserId)) {
            throw new InvalidArgumentException(
                "The Margin's loser Candidate had no id. No registry key could be formed."
            );
        }
        return array($winnerId, $loserId);
    }
    protected function makeKey(Candidate $winner, Candidate $loser) : string
    {
        list($winnerId, $loserId) = $this->getCandidateIds($winner, $loser);
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
}
