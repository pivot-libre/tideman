<?php
namespace PivotLibre\Tideman;
use PivotLibre\Tideman\CandidateTest;
use PivotLibre\Tideman\MarginTieBreaker;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\SingleBallotMarginTieBreaker;

/**
 * This class naively assumes that the provided Ballot establishes a total ordering over all of its CandidateTest
 * In other words, it assumes that there are no ties in the provided Ballot. The Margin whose
 */
class TotallyOrderedBallotMarginTieBreaker extends SingleBallotMarginTieBreaker {
    private $candidateComparator;
    public function __construct(Ballot $ballot) {
        parent::__construct($ballot);
        $this->candidateComparator = new CandidateComparator($ballot);
    }

    public function breakTie(Margin $a, Margin $b) : bool {
        $comparisonResult = $this->candidateComparator->compare($a->getWinner(), $b->getWinner());

        if (1 === $comparisonResult) {
            return TRUE;
        } else if (-1 === $comparisonResult) {
            return FALSE;
        } else if ( 0 == $comparisonResult) {
            $comparisonResult = $this->candidateComparator->compare($a->getLoser(), $b->getLoser());
            if (1 === $comparisonResult) {
                return TRUE;
            } else if (-1 === $comparisonResult) {
                return FALSE;
            } else {
                throw new InvalidArgumentException("The CandidateComparator returned an expected value: "
                    . "'$comparisonResult'. Should return either 1 or -1");
            }
        } else {
            throw new InvalidArgumentException("The CandidateComparator returned an expected value: "
                . "'$comparisonResult'. Should return either 1 or -1");
        }
    }
}
