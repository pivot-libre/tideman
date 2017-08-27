<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;
use PivotLibre\Tideman\Ballot;
use PivotLibre\Tideman\CandidateTest;
use PivotLibre\Tideman\MarginTieBreaker;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\SingleBallotMarginTieBreaker;

/**
 * This class breaks ties using a simplified version of the tie breaking rule published in Tideman and Zavist's 1989
 * paper "Complete Independence of Clones". The simplification is that this class requires that
 * the TBRC (aka Ballot) contains no ties.
 */
class TotallyOrderedBallotMarginTieBreaker implements MarginTieBreaker
{
    private $candidateComparator;

    /**
     * Construct a MarginTieBreaker using a CandidateComparator that establishes a total ordering over all Candidates.
     * In other words,the CandidateComparator should return nonzero integers for all Candidates, unless the two
     * Candidates being compared are actually the same Candidate as determined by comparing their Id properties:
     * ($candidateA->getId() == $candidateB->getId())
     * In other words, the CandidateComparator must use a Ballot that contains no ties.
     */
    public function __construct(CandidateComparator $candidateComparator)
    {
        if ($candidateComparator->getBallot()->containsTies()) {
            throw new InvalidArgumentException(
                "Could not construct TieBreaker. The TotallyOrderedBallotMarginTieBreaker requires that the Ballot"
                . " contain no ties."
            );
        } else {
            $this->candidateComparator = $candidateComparator;
        }
    }

    /**
     * Break the tie between the Margins by preferring the Margin whose winner ranks higher in a
     * tie-breaking Ballot. If the winners are the same in both Margins, resolve the tie by preferring the Margin whose
     * loser ranks higher in a tie-breaking Ballot. If the winner and the loser are the same in both Margins, throw an
     * InvalidArgumentException because they are the same Margin. If the Margin's difference properties are not equal,
     * throw an InvalidArgumentException because they are not tied.
     *
     * @return bool - true if Margin $a should be considered to be more preferred than Margin $b.
     *         false if Margin $b should be considered to be more preferred than Margin $a.
     */
    public function breakTie(Margin $a, Margin $b) : bool
    {
        //initialize to something invalid to ensure that a TypeError will be thrown on return if the body of the method
        //fails to assign a correct value;
        $aIsMorePreferredThanB = null;

        //if the Margins are actually tied
        if ($a->getDifference() === $b->getDifference()) {
            $comparisonResult = $this->candidateComparator->compare($a->getWinner(), $b->getWinner());
            if (1 === $comparisonResult) {
                $aIsMorePreferredThanB = true;
            } elseif (-1 === $comparisonResult) {
                $aIsMorePreferredThanB = false;
            } elseif (0 === $comparisonResult) {
                //the winners are tied
                if ($a->getWinner()->getId() === $b->getWinner()->getId()) {
                    //winners are the same, so we compare the losers
                    $comparisonResult = $this->candidateComparator->compare($a->getLoser(), $b->getLoser());
                    if (1 === $comparisonResult) {
                        $aIsMorePreferredThanB = true;
                    } elseif (-1 === $comparisonResult) {
                        $aIsMorePreferredThanB = false;
                    } else {
                        throw new InvalidArgumentException("Could not break tie. The CandidateComparator returned an "
                        . "unexpected value: '$comparisonResult' Should return either 1 or -1");
                    }
                } else {
                    throw new InvalidArgumentException(
                        "Could not break tie. The CandidateComparator indicated that two "
                        . "non-identical candidates were tied. The CandidateComparator is probably using a Ballot that "
                        . "has ties, whereas this TieBreaker expects such a Ballot to contain no ties."
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    "Could not break tie. The CandidateComparator returned an expected value:"
                    . "'$comparisonResult'. Should return either 1, 0, or -1"
                );
            }
        } else {
            throw new InvalidArgumentException(
                "Could not break tie. The parameterized Margins are not tied.\n$a\n$b\n"
            );
        }
        return $aIsMorePreferredThanB;
    }
}
