<?php
namespace PivotLibre\Tideman;

class Ballot extends GenericCollection
{
    /**
     * @param The most-preferred Candidates come first (low index). The least-preferred Candidates go
     * last (high index). Tied Candidates are in the same CandidateList.
     */
    public function __construct(CandidateList ...$listsOfCandidates)
    {
        $this->values = $listsOfCandidates;
    }

    /**
     * @param The most-preferred Candidates come first (low index). The least-preferred Candidates go
     * last (high index). This method cannot construct a Ballot that contains ties. To do that, use the
     * Ballot constructor instead.
     */
    public static function wrapEachInCandidateList(Candidate ...$candidates) : array
    {
        $candidateLists = [];
        foreach ($candidates as $candidate) {
            $candidateLists[] = new CandidateList($candidate);
        }
        return $candidateLists;
    }

   /**
     * @return
     *         TRUE if this Ballot contains ties
     *         FALSE if this Ballot contains no ties
     */
    public function containsTies() : bool
    {
        $candidateListLengths = array_map(
            function (CandidateList $candidateList) {
                return sizeof($candidateList->toArray());
            },
            $this->values
        );

        $numberOfNonTiedCandidates = sizeof(
            array_filter(
                $candidateListLengths,
                function (int $candidateListSize) {
                    return $candidateListSize === 1;
                }
            )
        );
        $containsTies = ($numberOfNonTiedCandidates !== sizeof($this->values));
        return $containsTies;
    }

    /**
     * @param Ballot to linearize
     * @return a CandidateList. All of the CandidateLists are of length 1.
     * The original ordering is unaffected.
     */
    protected function getCandidatesWithTiesRandomlyBroken() : CandidateList
    {
        $candidatesWithTiesBroken = [];
        foreach ($this->values as $candidatesWithSameRank) {
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
     * @return a copy of the current instance whose tied candidates have been put
     * in a random order. This method does not modify the current instance.
     */
    public function getCopyWithRandomlyResolvedTies() : Ballot
    {
        $candidatesWithTiesBroken = $this->getCandidatesWithTiesRandomlyBroken();
        $candidatesWrappedInLists = Ballot::wrapEachInCandidateList(...$candidatesWithTiesBroken);
        $copy = new Ballot(...$candidatesWrappedInLists);
        return $copy;
    }
}
