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

    /**
     * Register a Margin for later retrieval
     */
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

    /**
     * @return a clone of the Margin that corresponds to the parameterized winner and loser Candidate.
     * Since a clone is returned, modifications may be made without impacting the registered Margin.
     * If you need to update a Margin's difference, consider using MarginRegistry->incrementMargin()
     */
    public function get(Candidate $winner, Candidate $loser) : Margin
    {
        $margin = clone $this->getActualMargin($winner, $loser);
        return $margin;
    }

    /**
     * @return the original Margin that corresponds to the parameterized winner and loser Candidate.
     * Since the actual Margin object is returned, modifications made by the caller will impact the registered Margin.
     */
    private function getActualMargin(Candidate $winner, Candidate $loser) : Margin
    {
        $key = $this->makeKey($winner, $loser);
        if (!array_key_exists($key, $this->registry)) {
            throw new InvalidArgumentException(
                "No margin found for the given pair of Candidates."
            );
        } else {
            $margin = clone $this->registry[$key];
            return $margin;
        }
    }
    /**
     * @return the number of Margins that have been registered.
     */
    public function getCount() : int
    {
        return sizeof($this->registry);
    }

    public function incrementMargin(Candidate $winner, Candidate $loser, int $increment) : void
    {
        $margin = $this->getActualMargin($winner, $loser);
        $originalDifference = $margin->getDifference();
        $updatedDifference = $originalDifference + $increment;
        $margin->setDifference($updatedDifference);
    }
}
