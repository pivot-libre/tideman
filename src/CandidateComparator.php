<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

abstract class CandidateComparator
{
    protected $ballot;
    protected $candidateIdToRank;

    /**
     * @param NBallot. The CandidateComparator will store a copy of the Ballot. The caller of this constructor may
     * subsequently modify the parameterized Ballot without affecting this CandidateComparator.
     */
    public function __construct(NBallot $ballot)
    {
        $this->ballot = clone $ballot;
        $this->candidateIdToRank = $this->makeCandidateIdToRankMap($ballot);
    }

    /**
     * @return NBallot copy of the Ballot that this instance uses to inform its comparisons. The returned Ballot may be
     * modified without affecting this CandidateComparator.
     */
    public function getBallot() : NBallot
    {
        return clone $this->ballot;
    }

    /**
     * Return an associative array that maps Candidates' ids to an integer. The integer represents the rank of the
     * Candidate within the NBallot. A smaller integer indicates higher preference. An integer of zero is the most
     * preferred. Since a NBallot can contain ties, multiple Candidate ids can map to the same integer.
     * @param NBallot $ballot
     * @return array
     */
    protected function makeCandidateIdToRankMap(NBallot $ballot) : array
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
     * @param Candidate $a
     * @param Candidate $b
     * @return an int :
     *  a negative integer if Candidate $a is more preferred than Candidate $b
     *  0 if Candidate $a and Candidate $b are tied
     *  a postive integer if Candidate $b is more preferred than Candidate $a
     */
    public function compare(Candidate $a, Candidate $b) : int
    {
        $aRank = $this->getRank($a);
        $bRank = $this->getRank($b);

        $result = $aRank - $bRank;
        return $result;
    }

    /**
     * @param Candidate $candidate
     * @return int zero-based index of the candidate within the comparator's ballot
     */
    abstract protected function getRank(Candidate $candidate) : int;

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
