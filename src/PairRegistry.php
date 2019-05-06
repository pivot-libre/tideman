<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class PairRegistry implements \JsonSerializable
{
    private $registry = [];

    /**
     * Register a pair in the registry.
     * @param Pair $pair
     * @throws InvalidArgumentException if the pair has already been registered
     */
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

    /**
     * Gets the registered pair
     * @param Candidate $winner
     * @param Candidate $loser
     * @return Pair
     * @throws InvalidArgumentException if the pair of Candidates has not been registered
     *
     */
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

    /**
     * @param Candidate $winner
     * @param Candidate $loser
     * @return bool true if the specified Pair is registered, false otherwise
     */
    public function contains(Candidate $winner, Candidate $loser)
    {
        try {
            $this->get($winner, $loser);
            $pairIsRegistered = true;
        } catch (InvalidArgumentException $ex) {
            $pairIsRegistered = false;
        }
        return $pairIsRegistered;
    }

    /**
     * returns number of Pairs that has been registered.
     * @return int
     */
    public function getCount() : int
    {
        //there is no good recursive count implementation in php, so we improvise one:
        $count = 0;
        array_walk_recursive($this->registry, function ($pair) use (&$count) {
            $count++;
        });
        return $count;
    }

    /**
     * Get a list of all pairs that have been registered.
     * @return PairList
     */
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

    /**
     * Get a list of dominating Pairs. A Pair comparing A to B is considered to be dominating if its
     * `getVotes()` value is greater than the `getVotes()` value for the Pair comparing B to A. If two Pairs comparing
     * the same candidate have the same nonzero `getVotes()` value, then one will be considered to dominate the other
     * arbitrarily. If a Pair's `getVotes()` value is zero, it is not considered to be a dominating Pair. At most half
     * of the Pairs registered in this registry will be returned.
     *
     * For example, if the following winning vote-style Pairs had been registered:
     * (A--(10)-->B)
     * (B--(5)-->A)
     * Then this method would return:
     * [ (A--(10)-->B) ]
     * Because 10 > 5
     *
     * Similarly, if the following margin-style Pairs had been registered:
     * (A--(10)-->B)
     * (B--(-10)-->A)
     * Then this method would return:
     * [ (A--(10)-->B) ]
     * Because 10 > -10
     *
     * If two Pairs comparing the same two candidates have the same nozero `getVotes()`, then one is chosen arbitrarily
     * because they are the same.
     * For example, if these two Pairs had been registered:
     * (A--(10)-->B)
     * (B--(10)-->A)
     * Then either one of these two results could be returned, but not both:
     * [ (A--(10)-->B) ]
     * [ (B--(10)-->A) ]
     *
     * If a Pair has a zero `getVotes()` value, it is not returned.
     * For example, if these two Pairs had been registered:
     * (A--(0)-->B)
     * (B--(0)-->A)
     * Then neither one of these two results could be returned. Instead an empty PairList would be returned:
     * []
     *
     * @return PairList of dominating pairs
     */
    public function getDominatingPairs() : PairList
    {
        $dominatingPairs = [];
        //In case two Pairs compare the same candidates and have the same `getVotes()`, we should only return one Pair
        $tiesAlreadyAdded = new PairRegistry();
        foreach ($this->getAll() as $pair) {
            if (0 != $pair->getVotes()) {
                $oppositePair = $this->get($pair->getLoser(), $pair->getWinner());

                if ($pair->getVotes() > $oppositePair->getVotes()) {
                    $dominatingPairs[] = $pair;
                } elseif ($pair->getVotes() === $oppositePair->getVotes()) {
                    //handle ties
                    //check winner-->loser
                    $pairRegistered = $tiesAlreadyAdded->contains($pair->getWinner(), $pair->getLoser());
                    //check loser-->winner
                    $oppositeRegistered = $tiesAlreadyAdded->contains($pair->getLoser(), $pair->getWinner());
                    $alreadyRegistered = $pairRegistered || $oppositeRegistered;
                    if (!$alreadyRegistered) {
                        $dominatingPairs[] = $pair;
                        $tiesAlreadyAdded->register($pair);
                    }
                }
                //there's no need for a separate condition for $pair->getVotes() < $oppositePair->getVotes() because
                //that condition will be addressed in a subsequent iteration
            }
        }
        $dominatingPairsList = new PairList(...$dominatingPairs);
        return $dominatingPairsList;
    }

    /**
     * @return array<string, array<string, Pair>> The outer keys are the winning candidate ids.
     *      The inner keys are the losing candidate ids.
     */
    public function asArray() : array
    {

        return $this->registry;
    }
    
    /**
     * @see PairRegistry::asArray()
     */
    public function jsonSerialize()
    {
        return $this->registry;
    }
}
