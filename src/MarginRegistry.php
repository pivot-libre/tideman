<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class MarginRegistry
{
    private $registry = [];

    public function register(Margin $margin) : void
    {
        $winnerId = $margin->getWinner()->getId();
        $loserId = $margin->getLoser()->getId();

        //if there is no array for Margins with $winnerId as their Id, make one.
        if (!array_key_exists($winnerId, $this->registry)) {
            $this->registry[$winnerId] = [];
        }
        //if a margin of the same winner and loser exists in the inner array
        if (array_key_exists($loserId, $this->registry[$winnerId])) {
            throw new InvalidArgumentException(
                "This Margin has already been registered: $margin"
            );
        } else {
            $this->registry[$winnerId][$loserId] = $margin;
        }
    }
    public function get(Candidate $winner, Candidate $loser) : Margin
    {
        $winnerId = $winner->getId();
        $loserId = $loser->getId();

        if (!array_key_exists($winnerId, $this->registry)
            || !array_key_exists($loserId, $this->registry[$winnerId])) {
            $marginAsString = "$winnerId -> $loserId";
            throw new InvalidArgumentException(
                "No margin found for the given pair of Candidates. $marginAsString"
            );
        } else {
            $margin = $this->registry[$winnerId][$loserId];
            return $margin;
        }
    }
    public function getCount() : int
    {
        //there is no good recursive count implementation in php, so we improvise one:
        $count = 0;
        array_walk_recursive($this->registry, function ($margin) use (&$count) {
            $count++;
        });
        return $count;
    }
    public function getAll() : MarginList
    {
        //there is no flat map function in php, so we improvise one:
        $marginsOnly = [];
        array_walk_recursive($this->registry, function (&$margin) use (&$marginsOnly) {
            $marginsOnly[] = $margin;
        });
            
        $marginsList = new MarginList(...$marginsOnly);
        return $marginsList;
    }
}
