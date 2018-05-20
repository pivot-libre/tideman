<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class PairRegistry
{
    private $registry = [];

    public function register(Pair $pair) : void
    {
        $winnerId = $pair->getWinner()->getId();
        $loserId = $pair->getLoser()->getId();

        //if there is no array for Pairs with $winnerId as their Id, make one.
        if (!array_key_exists($winnerId, $this->registry)) {
            $this->registry[$winnerId] = [];
        }
        //if a pair of the same winner and loser exists in the inner array
        if (array_key_exists($loserId, $this->registry[$winnerId])) {
            throw new InvalidArgumentException(
                "This Pair has already been registered: $pair"
            );
        } else {
            $this->registry[$winnerId][$loserId] = $pair;
        }
    }
    public function get(Candidate $winner, Candidate $loser) : Pair
    {
        $winnerId = $winner->getId();
        $loserId = $loser->getId();

        if (!array_key_exists($winnerId, $this->registry)
            || !array_key_exists($loserId, $this->registry[$winnerId])) {
            $pairAsString = "$winnerId -> $loserId";
            throw new InvalidArgumentException(
                "No pair found for the given pair of Candidates. $pairAsString"
            );
        } else {
            $pair = $this->registry[$winnerId][$loserId];
            return $pair;
        }
    }
    public function getCount() : int
    {
        //there is no good recursive count implementation in php, so we improvise one:
        $count = 0;
        array_walk_recursive($this->registry, function ($pair) use (&$count) {
            $count++;
        });
        return $count;
    }
    public function getAll() : PairList
    {
        //there is no flat map function in php, so we improvise one:
        $pairsOnly = [];
        array_walk_recursive($this->registry, function (&$pair) use (&$pairsOnly) {
            $pairsOnly[] = $pair;
        });
            
        $pairsList = new PairList(...$pairsOnly);
        return $pairsList;
    }
}
