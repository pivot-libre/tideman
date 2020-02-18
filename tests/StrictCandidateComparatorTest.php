<?php
namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class StrictCandidateComparatorTest extends CandidateComparatorTest
{
    protected $concreteType = StrictCandidateComparator::class;

    public function testCompareWithEmptyBallot() : void
    {
        $instance = new $this->concreteType(new Ballot());
        $this->expectException(InvalidArgumentException::class);
        $instance->compare($this->alice, $this->bob);
    }
    public function testCompareOneCandidateOnBallotWithOneCandidateOffBallot() : void
    {
        $instance = new $this->concreteType(new Ballot(
            new CandidateList(
                $this->alice
            )
        ));
        $this->expectException(InvalidArgumentException::class);
        $instance->compare($this->alice, $this->bob);
    }
}
