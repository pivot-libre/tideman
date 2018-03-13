<?php
namespace PivotLibre\Tideman\TieBreaking;

use \InvalidArgumentException;
use PivotLibre\Tideman\Ballot;
use PivotLibre\Tideman\Margin;
use PivotLibre\Tideman\CandidateTest;
use PivotLibre\Tideman\TieBreaking\MarginTieBreaker;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\SingleBallotMarginTieBreaker;

/**
 * This class breaks ties using a simplified version of the tie-breaking rule published in Tideman and Zavist's 1989
 * paper "Complete Independence of Clones". The simplification is that this class requires that
 * the TBRC (aka Ballot) contains no ties.
 */
class TotallyOrderedBallotMarginTieBreaker implements MarginTieBreaker
{
    private $candidateComparator;

    /**
     * Construct a MarginTieBreaker using a CandidateComparator that establishes a total ordering over all Candidates.
     * The CandidateComparator must use a Ballot that contains no ties.
     * In other words,the CandidateComparator should return nonzero integers for all Candidates, unless the two
     * Candidates being compared are actually the same Candidate as determined by comparing their Id properties:
     * ($candidateA->getId() == $candidateB->getId())
     *
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
     * @return int
     *   a negative int if $a is preferred over $b
     *   a postive int if $b is preferred over $a.
     */
    public function breakTie(Margin $a, Margin $b) : int
    {
        //initialize to something invalid to ensure that a TypeError will be thrown on return if the body of the method
        //fails to assign a correct value;
        $returnValue = null;

        //if the Margins are actually tied
        if ($a->getDifference() === $b->getDifference()) {
            $comparisonResult = $this->candidateComparator->compare($a->getWinner(), $b->getWinner());
            if (0 === $comparisonResult) {
                //the winners are tied
                //winners are the same, so we compare the losers
                $comparisonResult = $this->candidateComparator->compare($a->getLoser(), $b->getLoser());
            }
            $returnValue = $comparisonResult;
        } else {
            throw new InvalidArgumentException(
                "Could not break tie. The parameterized Margins are not tied.\n$a\n$b\n"
            );
        }
        return $returnValue;
    }
}
