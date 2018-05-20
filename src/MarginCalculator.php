<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\PairRegistry;


class MarginCalculator
{
    /**
     * Register a Pair for all possible pairs of Candidates described in an Agenda. If the agenda contains N
     * Candidates, then this method should register (N^2) - N = N(N - 1) Candidates.
     *
     * @todo This basically registers all non-duplicating permutations of a list of Candidates. Consider moving this to
     * a more generic function. http://php.net/manual/en/language.generators.syntax.php
     */
    public function initializeRegistry(Agenda $agenda) : PairRegistry
    {
        $registry = new PairRegistry();
        foreach ($agenda->getCandidates() as $outerCandidate) {
            foreach ($agenda->getCandidates() as $innerCandidate) {
                /**
                 * It only makes sense to compare the support between two DIFFERENT candidates.
                 * It doesn't make sense to compare support of a candidate and themself.
                 */
                if ($outerCandidate->getId() != $innerCandidate->getId()) {
                    $pair = new Pair($outerCandidate, $innerCandidate, 0);
                    $registry->register($pair);
                }
            }
        }
        return $registry;
    }

    /**
     *
     * Adds the $amountToAdd to the count already associated with the appropriate Pair in the
     * PairRegistry.
     *
     * @param Candidate $winner
     * @param Candidate $loser
     * @param PairRegistry $registry
     * @param int $amountToAdd
     */
    public function incrementPairInRegistry(
        Candidate $winner,
        Candidate $loser,
        PairRegistry $registry,
        int $amountToAdd
    ) : void {
        $pairToUpdate = $registry->get($winner, $loser);
        $updatedPair = $pairToUpdate->getVotes() + $amountToAdd;
        $pairToUpdate->setVotes($updatedPair);
    }


    /**
     * Create the pairwise comparisons of popular support, a.k.a. the Pairs.
     *
     * @param Agenda $agenda a set of candidates. This is a non-strict subset of the Candidates in $nBallots.
     * @param ...NBallot $nBallots a list of NBallots. The set of Candidates in $nBallots is a non-strict
     * superset of the Candidates in $agenda.
     * @return a PairRegistry whose Pairs completely completely compare popular support between every Candidate.
     * The number of Pairs in the returned PairRegistry should be equal to `N(N - 1)`, where `N` is the number of
     * Candidates in $agenda.
     *
     * @todo this function generates all non-duplicating combinations of Candidates. Consider moving the combination
     * logic elsewhere. http://php.net/manual/en/language.generators.syntax.php
     */
    public function calculate(Agenda $agenda, NBallot ...$nBallots) : PairRegistry
    {
        $registry = $this->initializeRegistry($agenda);

        foreach ($nBallots as $nBallot) {
            $comparator = new CandidateComparator($nBallot);
            $ballotCount = $nBallot->getCount();
            $candidatesList = $agenda->getCandidates();
            $candidates = $candidatesList->toArray();
            $candidatesCount = count($candidates);
            //Loop through all combinations of candidates in the Agenda.
            for ($outerCounter = 0; $outerCounter < $candidatesCount; ++$outerCounter) {
                for ($innerCounter = $outerCounter + 1; $innerCounter < $candidatesCount; ++$innerCounter) {
                    $outerCandidate = $candidates[$outerCounter];
                    $innerCandidate = $candidates[$innerCounter];
                    $sortedCandidates = [$outerCandidate, $innerCandidate];
                    usort($sortedCandidates, $comparator);
                    list($winner, $loser) = $sortedCandidates;
                    $updateAmount = $ballotCount;
                    $this->incrementPairInRegistry(
                        $winner,
                        $loser,
                        $registry,
                        $updateAmount
                    );

                }
            }
        }
        return $registry;
    }
}
