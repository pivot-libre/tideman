<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class CandidateComparator
{
    private $ballot;
    private $candidateIdToRank;

    /**
     * @param a Ballot. The CandidateComparator will store a copy of the Ballot. The caller of this constructor may
     * subsequently modify the parameterized Ballot without affecting this CandidateComparator.
     */
    public function __construct(Ballot $ballot)
    {
        $this->ballot = clone $ballot;
        $this->candidateIdToRank = $this->makeCandidateIdToRankMap($ballot);
    }

    /**
     * @return a copy of the Ballot that this instance uses to inform its comparisons. The returned Ballot may be
     * modified without affecting this CandidateComparator.
     */
    public function getBallot() : Ballot
    {
        return clone $this->ballot;
    }
    /**
     * Return an associative array that maps Candidates' ids to an integer. The integer represents the rank of the
     * Candidate within the Ballot. A smaller integer indicates higher preference. An integer of zero is the most
     * preferred. Since a Ballot can contain ties, multiple Candidate ids can map to the same integer.
     */
    private function makeCandidateIdToRankMap(Ballot $ballot) : array
    {
        $this->candidateIdToRank = array();
        foreach ($ballot as $rank => $candidateList) {
            foreach ($candidateList as $candidate) {
                $candidateId = $candidate->getId();
                if (isset($this->candidateIdToRank[$candidateId])) {
                    throw new InvalidArgumentException(
                        "A Ballot cannot contain a candidate more than once."
                        . " Offending Ballot: " . var_export($ballot, true)
                        . " Offending Candidate: " . var_export($candidate, true)
                    );
                } else {
                    $this->candidateIdToRank[$candidateId] = $rank;
                }
            }
        }
        return $this->candidateIdToRank;
    }

     /**
      * Return an associative array that maps Candidates' ids to an integer. The integer represents the rank of the
      * Candidate within the Ballot. A smaller integer indicates higher preference. An integer of zero is the most
      * preferred. Since a Ballot can contain ties, multiple Candidate ids can map to the same integer.
      *
      * The array returned is a clone of this instance's private copy, so the result can be modifed without impacting
      * the comparisons performed by this instance.
      */
    public function getCandidateIdToRankMap() : array
    {
        $copy = $this->candidateIdToRank;
        return $copy;
    }

    /**
     * @param Candidate $a
     * @param Candidate $b
     * @return an int :
     *  a negative integer if Candidate $a is more preferred than Candidate $b
     *  0 if Candidate $a and Candidate $b are tied
     *  a postive integer if Candidate $b is more preferred than Candidate $a
     */
    public function compare(Candidate $a, Candidate $b) : int
    {
        $aId = $a->getId();
        $bId = $b->getId();
        if (!isset($this->candidateIdToRank[$aId])) {
            throw new InvalidArgumentException(
                "Candidate's Id should be in the map of ID to Rank.\n"
                . " Candidate: " . $a . "\n"
                . " Mapping: " . var_export($this->candidateIdToRank, true)
            );
        } elseif (!isset($this->candidateIdToRank[$bId])) {
            throw new InvalidArgumentException(
                "Candidate's Id should be in the map of ID to Rank.\n"
                . " Candidate: " . $b . "\n"
                . " Mapping: " . var_export($this->candidateIdToRank, true)
            );
        } else {
            $aRank = $this->candidateIdToRank[$aId];
            $bRank = $this->candidateIdToRank[$bId];

            $result = $aRank - $bRank;
            return $result;
        }
    }

    /**
     * A simple wrapper that simplifies referencing this instance's compare() method.
     * For example, the wrapper permits us to write:
     *
     * $tieBreaker = new MyGreatTieBreaker();
     * usort($array, new CandidateComparator($tieBreaker));
     *
     * instead of:
     *
     * $tieBreaker = new MyGreatTieBreaker();
     * usort($array, array(new CandidateComparator($tieBreaker), "compare"));
     *
     * Additional details:
     * https://stackoverflow.com/a/35277180
     */
    public function __invoke(Candidate $a, Candidate $b) : int
    {
        return $this->compare($a, $b);
    }
}
