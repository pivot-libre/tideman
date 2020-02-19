<?php
namespace PivotLibre\Tideman;

class TolerantCandidateComparatorTest extends CandidateComparatorTest
{
    protected $concreteType = TolerantCandidateComparator::class;

    public function testOneCandidateNotInBallotIsConsideredLastPlace() : void
    {
        $instance = new TolerantCandidateComparator(
            new Ballot(
                new CandidateList($this->claire),
                new CandidateList($this->alice)
            )
        );
        //sanity check
        $this->assertEquals(-1, $instance->compare($this->claire, $this->alice));
        //now assert that candidates not on the ballot are considered last place
        $this->assertEquals(-2, $instance->compare($this->claire, $this->bob));
        $this->assertEquals(-1, $instance->compare($this->alice, $this->bob));
    }

    public function testTwoCandidatesNotInBallotAreTiedForLastPlace() : void
    {
        $instance = new TolerantCandidateComparator(
            new Ballot(
                new CandidateList($this->claire)
            )
        );

        //sanity check
        $this->assertEquals(-1, $instance->compare($this->claire, $this->alice));

        //now assert that candidates not on the ballot are considered last place
        $this->assertEquals(-1, $instance->compare($this->claire, $this->bob));
        $this->assertEquals(1, $instance->compare($this->bob, $this->claire));

        //assert that two candidates not on the ballot are considered tied
        $this->assertEquals(-1, $instance->compare($this->claire, $this->alice));
        $this->assertEquals(1, $instance->compare($this->alice, $this->claire));

        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
    }
}
