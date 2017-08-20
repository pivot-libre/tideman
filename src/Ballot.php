<?php
namespace PivotLibre\Tideman;

class Ballot extends GenericCollection
{
    public function __construct(CandidateList ...$listsOfCandidates)
    {
        $this->values = $listsOfCandidates;
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
}
