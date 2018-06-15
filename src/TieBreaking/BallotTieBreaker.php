<?php
namespace PivotLibre\Tideman\TieBreaking;

use PivotLibre\Tideman\Ballot;
use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\CandidateList;

class BallotTieBreaker
{

    /**
     * @param The most-preferred Candidates come first (low index). The least-preferred Candidates go
     * last (high index). This method cannot construct a Ballot that contains ties. To do that, use the
     * Ballot constructor instead.
     */
    protected function wrapEachInCandidateList(Candidate ...$candidates) : array
    {
        $candidateLists = [];
        foreach ($candidates as $candidate) {
            $candidateLists[] = new CandidateList($candidate);
        }
        return $candidateLists;
    }

    /**
     * @param Ballot $ballot. The ordering of candidates in the Ballot is unaffected by this method.
     * @return CandidateList a total ordering of the Candidates in the Ballot (no ties).
     *
     */
    protected function getCandidatesWithTiesRandomlyBroken(Ballot $ballot) : CandidateList
    {
        $candidatesWithTiesBroken = [];
        foreach ($ballot as $candidatesWithSameRank) {
            $candidatesWithSameRankArray = $candidatesWithSameRank->toArray();
            if (1 == sizeof($candidatesWithSameRankArray)) {
                $candidatesWithTiesBroken[] = $candidatesWithSameRankArray[0];
            } else {
                //randomize the order of the tied candidates
                shuffle($candidatesWithSameRankArray);
                array_push($candidatesWithTiesBroken, ...$candidatesWithSameRankArray);
            }
        }
        // now build a ballot
        $candidateLists = [];
        foreach ($candidatesWithTiesBroken as $candidate) {
            $candidateLists[] = new CandidateList($candidate);
        }
        return new CandidateList(...$candidatesWithTiesBroken);
    }

    /**
     * @return a new Ballot whose tied candidates have been put
     * in a random order. This method does not modify the parameterized Ballot.
     */
    public function breakTiesRandomly(Ballot $ballotWithTies) : Ballot
    {
        $candidatesWithTiesBroken = $this->getCandidatesWithTiesRandomlyBroken($ballotWithTies);
        $candidatesWrappedInLists = $this->wrapEachInCandidateList(...$candidatesWithTiesBroken);
        $ballotWithoutTies = new Ballot(...$candidatesWrappedInLists);
        return $ballotWithoutTies;
    }
}
