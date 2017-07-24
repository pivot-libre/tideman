<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;
use \InvalidArgumentException;

class MarginCalculator
{
    public function __construct()
    {
        /**
         * @todo #7 Decide whether the NBallots should be passed to the calculator
         * at instantiation, or when invoking calculate().
         */
    }


    /**
     * Register a Margin for all possible pairs of Candidates described in an Agenda. If the agenda contains N
     * Candidates, then this method should register (N^2) - N Candidates.
     *
     * @todo #7 Decide whether this method should be a part of the MarginCalculator class or the Agenda class.
     */
    protected function initializeRegistry(Agenda $agenda) : MarginRegistry
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
                }
            }
        }
        return $registry;
    }
    /**
     * Return an associative array that maps Candidates' ids to an integer. The integer represents the rank of the
     * Candidate within the Ballot. A lower index indicates higher preference. An index of zero is the highest rank.
     * Since a Ballot can contain ties, multiple Candidate ids can map to the same integer rank.
     */
    public function getCandidateIdToRankMap(Ballot $ballot) : array
    {
        $candidateIdToRank = array();
        foreach ($ballot as $rank => $candidateList) {
            foreach ($candidateList as $candidate) {
                $candidateId = $candidate->getId();
                if (empty($candidateId)) {
                    throw new InvalidArgumentException("Candidates must have a non-empty Id");
                } else {
                    if (isset($candidateIdToRank[$candidateId])) {
                        throw new InvalidArgumentException(
                            "A Ballot cannot contain a candidate more than once."
                            . " Offending Ballot: " . $ballot
                            . " Offending Candidate: " . $candidate
                        );
                    } else {
                        $candidateIdToRank[$candidateId] = $rank;
                    }
                }
            }
        }
        return $candidateIdToRank;
    }

    /**
     * @return array whose zeroth element is the winning candidate, and whose
     * first element is the loser. It is suggested that the results of this function be assigned via list().
     * Example:
     * list($winner, $loser) = getWinnerAndLoser(...);
     */
    public function getWinnerAndLoser(
        Candidate $outerCandidate,
        Candidate $innerCandidate,
        array $candidateIdToRank
    ) : array {
        $outerCandidateId = $outerCandidate->getId();
        $innerCandidateId = $innerCandidate->getId();
        if (empty($outerCandidateId)) {
            throw new InvalidArgumentException("Outer Candidate Id should be non-empty.");
        } elseif (empty($innerCandidateId)) {
            throw new InvalidArgumentException("Inner Candidate Id should be non-empty.");
        } else {
            if (!isset($candidateIdToRank[$outerCandidateId])) {
                throw new InvalidArgumentException(
                    "Candidate's Id should be in the map of ID to Rank.\n"
                    . " Candidate: " . $outerCandidate . "\n"
                    . " Mapping: " . var_export($candidateIdToRank, true)
                );
            } elseif (!isset($candidateIdToRank[$innerCandidateId])) {
                throw new InvalidArgumentException(
                    "Candidate's Id should be in the map of ID to Rank.\n"
                    . " Candidate: " . $innerCandidate . "\n"
                    . " Mapping: " . var_export($candidateIdToRank, true)
                );
            } else {
                $outerCandidateRank = $candidateIdToRank[$outerCandidateId];
                $innerCandidateRank = $candidateIdToRank[$innerCandidateId];

                //the candidate with the lower rank is the winner
                if ($outerCandidateRank < $innerCandidateRank) {
                    $winnerAndLoser = array($outerCandidate, $innerCandidate);
                } else {
                    /**
                     * no need to explicitly handle special case $outerCandidateRank == $innerCandidateRank
                     * One margin for the tied pair will be populated on the first iteration through candidates.
                     * The other margin will be populated on the the second iteration through candidates. At that point,
                     * the candidate arguments will be transposed.
                     */
                    $winnerAndLoser = array($innerCandidate, $outerCandidate);
                }
                return $winnerAndLoser;
            }
        }
    }
    /**
     * Uses the parameterized candidates and the $candidateIdToRank map to determine which candidate ranks higher.
     * Subsequently adds the $ballotCount to the count already associated with the appropriate margin in the
     * Registry.
     *
     * @param Candidate $winner
     * @param Candidate $loser
     * @param MarginRegistry $registry
     * @param int $ballotCount
     */
    protected function updatePairInRegistry(
        Candidate $winner,
        Candidate $loser,
        MarginRegistry $registry,
        int $ballotCount
    ) : void {
        $marginToUpdate = $registry->get($winner->getId(), $loser->getId());
        $updatedMargin = $marginToUpdate->getMargin() + $ballotCount;
        $marginToUpdate->setMargin($updatedMargin);
    }
    public function calculate(Agenda $agenda, NBallot ...$nBallots) : MarginRegistry
    {
        $registry = $this->initializeRegistry($agenda);

        foreach ($nBallots as $nBallot) {
            //a map of candidate id to their integer rank
            $candidateIdToRank = $this->getCandidateIdToRankMap($nBallot);
            $ballotCount = $nBallot->getCount();
            foreach ($agenda->getCandidates() as $outerCandidate) {
                foreach ($agenda->getCandidates() as $innerCandidate) {
                    if ($outerCandidate != $innerCandidate) {
                        list($winner, $loser) = $this->getWinnerAndLoser(
                            $outerCandidate,
                            $innerCandidate,
                            $candidateIdToRank
                        );
                        $this->updatePairInRegistry(
                            $winner,
                            $loser,
                            $registry,
                            $ballotCount
                        );
                    }
                }
            }
        }
        return $registry;
    }
}
