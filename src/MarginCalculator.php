<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\MarginRegistry;

class MarginCalculator
{
    public function __construct()
    {
        /**
         * @todo #7 Decide whether the NBallots should be passed to the calculator
         * at instantiation, or when invoking calculate().
         */
    }

    protected function initializeRegistry(Agenda $agenda) : MarginRegistry
    {
        $registry = new MarginRegistry();
        /**
         * @todo #7 register all possible pairs of candidates as a margin within the margin registry
         */
        return $registry;
    }

    protected function getCandidateIdToRankMap(Ballot $ballot) : array
    {
        $candidateIdToRank = array();
        /**
         * @todo #7 build map of candidate id to rank
         */

         //for each of the NBallot's candidate lists with counter i
             //for each Candidate in each Candidate list,
                 //ensure candidate's id is defined and non-empty
                 //$candidateToRank[$candidate->getId()] = i;
        return $candidateIdToRank;
    }
    protected function updatePairInRegistry(
        Candidate $outerCandidate,
        Candidate $innerCandidate,
        array $candidateIdToRank,
        MarginRegistry $registry
    ) : void {
        //if $outerCandidate != $innerCandidate
            //$outerCandidateRank = $candidateToRank[$outerCandidate->getId()];
            //$innerCandidateRank = $candidateToRank[$innerCandidate->getId()];
            // $marginToUpdate;
            // $updateAmount = $nBallot->getCount();

            //if($outerCandidateRank > $innerCandidateRank) {
                //$marginToUpdate = $marginRegistry($outerCandidate->getId(), $innerCandidate->getId());
            // } else {
                //no need to explicitly handle special case $outerCandidateRank == $innerCandidateRank
                //One margin for the tied pair will be populate ond the first pass through.
                //The other margin will be populated on the second pass through.
                //$marginToUpdate = $marginRegistry($innerCandidate->getId(), $outerCandidate->getId());
            // }
            //
    }
    public function calculate(Agenda $agenda, NBallot ...$nBallots) : MarginRegistry
    {
        $registry = $this->initializeRegistry($agenda);

        foreach ($nBallots as $nBallot) {
            //a map of candidate id to their integer rank
            $candidateIdToRank = $this->getCandidateIdToRankMap($nBallot);
            foreach ($agenda->getCandidates() as $outerCandidate) {
                foreach ($agenda->getCandidates() as $innerCandidate) {
                    $this->updatePairInRegistry($outerCandidate, $innerCandidate, $candidateIdToRank, $registry);
                }
            }
        }
        return $registry;
    }
}
