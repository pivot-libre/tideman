<?php
namespace PivotLibre\Tideman;
use PivotLibre\Tideman\Candidate;

class CandidateSet extends GenericCollection
{
    public function __construct(Candidate ...$candidates)
    {
            $this->add(...$candidates);
    }

    public function remove(Candidate ...$candidates) {
        foreach ($candidates as $candidate) {
            $key = $this->makeKey($candidate);
            unset($this->values[$key]);
        }
    }

    /**
     * If Canddiate is already present,then it is overwritten with the newer one
     */
    public function add(Candidate ...$candidates) {
        foreach ($candidates as $candidate) {
            $key = $this->makeKey($candidate);
            $this->values[$key] = $candidate;
        }
    }

    protected function makeKey(Candidate $candidate) : string
    {
        $key = $candidate->getId();
    }

    /**
     * @return the Candidate if present, or null if no such Candidate is in this Set.
     */
    public function get(Candidate $candidate) : Candidate
    {
        return $this->values[$this->makeKey($candidate)] ?? null;
    }
    /**
     * @return the Candidate if present, or null if no such Candidate is in this Set.
     */
//     public function getById(string $candidateId) : Candidate
//     {
//         return $this->values[$candidateId] ?? null;
// -   }
    public function contains(Candidate $candidate) : bool
    {
        return null == $this->get($candidate);
    }
}
