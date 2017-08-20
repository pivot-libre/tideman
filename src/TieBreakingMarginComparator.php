<?php
namespace PivotLibre\Tideman;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TieBreakingMarginComparator {

    use LoggerAwareTrait;

    private $tieBreaker;

    public function __construct(MarginTieBreaker $tieBreaker) {
        $this->tieBreaker = $tieBreaker;
    }

    /**
     * Compares two Margins, returning an integer to indicate their relative ordering.
     * @param Margin $a
     * @param Margin $b
     * @return an int :
     *  A positive int if Margin $a is more preferred than Margin $b
     *  A negative int if Margin $b is more preferred than Margin $a
     *
     * This function should never return zero. In the event of a tie, it should use the TieBreaker specified in the
     * constructor to determine a nonzero integer indicating which Margin should be treated as though it were more
     * preferred than the other.
     */
    public function compare(Margin $a, Margin $b) : int
    {
        $differenceOfStrength = $a->getDifference() - $b->getDifference();
        if( 0 == $differenceOfStrength) {
            $this->logger->info("Tie between two Margins:\n$a\n$b\n");
            $aMorePreferredThanB = $this->tieBreaker($a, $b);
            if ($aMorePreferredThanB) {
                $result = 1;
                $this->logger->info("Tie-breaking results:\nWinner:\n$a\nLoser:$b\n");
            } else {
                $result = -1;
                $this->logger->info("Tie-breaking results:\nWinner:\n$b\nLoser:$a\n");
            }
        } else {
            $result = $differenceOfStrength;
        }
        return $result;
    }

    /**
     * A simple wrapper that simplifies referencing this instance's compare() method.
     * For example, the wrapper permits us to write:
     *
     * $tieBreaker = new MyGreatTieBreaker();
     * usort($array, new TieBreakingMarginComparator($tieBreaker));
     *
     * instead of:
     *
     * $tieBreaker = new MyGreatTieBreaker();
     * usort($array, array(new TieBreakingMarginComparator($tieBreaker), "compare"));
     *
     * Additional details:
     * https://stackoverflow.com/a/35277180
     */
    public function __invoke(Margin $a, Margin $b) : int
    {
        return $this->compare($a, $b);
    }
}
