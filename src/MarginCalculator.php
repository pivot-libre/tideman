<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

use \InvalidArgumentException;

class MarginCalculator
{
    /**
     * Register a Margin for all possible pairs of Candidates described in an Agenda. If the agenda contains N
     * Candidates, then this method should register (N^2) - N = N(N - 1) Candidates.
     *
     * @todo #7 Decide whether this method should be a part of the MarginCalculator class or the Agenda class.
     */
    public function initializeRegistry(Agenda $agenda) : MarginRegistry
    {
        $registry = new MarginRegistry();
        foreach ($agenda->getCandidates() as $outerCandidate) {
            foreach ($agenda->getCandidates() as $innerCandidate) {
                /**
                 * It only makes sense to keep track of the difference of public support between
                 * two DIFFERENT candidates. It doesn't make sense to keep track of the difference between
                 * public support of a candidate and themself.
                 */
                if ($outerCandidate->getId() != $innerCandidate->getId()) {
                    $margin = new Margin($outerCandidate, $innerCandidate, 0);
                    $registry->register($margin);
                }
            }
        }
        return $registry;
    }

    /**
     *
     * Adds the $amountToAdd to the count already associated with the appropriate Margin in the
     * MarginRegistry.
     *
     * @param Candidate $winner
     * @param Candidate $loser
     * @param MarginRegistry $registry
     * @param int $amountToAdd
     */
    public function incrementMarginInRegistry(
        Candidate $winner,
        Candidate $loser,
        MarginRegistry $registry,
        int $amountToAdd
    ) : void {
        $marginToUpdate = $registry->get($winner, $loser);
        $updatedMargin = $marginToUpdate->getDifference() + $amountToAdd;
        $marginToUpdate->setDifference($updatedMargin);
    }
    /**
     * @return a MarginRegistry whose Margins completely describe the pairwise
     * difference in popular support between every Candidate.
     */
    public function calculate(NBallot ...$nBallots) : MarginRegistry
    {
        $agenda = new Agenda(...$nBallots);
        $registry = $this->initializeRegistry($agenda);

        foreach ($nBallots as $nBallot) {
            $comparator = new CandidateComparator($nBallot);
            $ballotCount = $nBallot->getCount();
            $candidatesList = $agenda->getCandidates();
            //it is very important to convert this to array, otherwise count() will always return 1
            $candidates = $candidatesList->toArray();
            $candidatesCount = count($candidates);
            //Loop through all combinations of candidates in the Agenda.
            for ($outerCounter = 0; $outerCounter < $candidatesCount; ++$outerCounter) {
                for ($innerCounter = $outerCounter + 1; $innerCounter < $candidatesCount; ++$innerCounter) {
                    $outerCandidate = $candidates[$outerCounter];
                    $innerCandidate = $candidates[$innerCounter];
                    $comparisonFactor = $comparator->compare($outerCandidate, $innerCandidate);
                    $updateAmount = $comparisonFactor * $ballotCount;
                    $this->incrementMarginInRegistry(
                        $outerCandidate,
                        $innerCandidate,
                        $registry,
                        $updateAmount
                    );
                    $this->incrementMarginInRegistry(
                        $innerCandidate,
                        $outerCandidate,
                        $registry,
                        -1 * $updateAmount
                    );
                }
            }
        }
        return $registry;
    }
}
