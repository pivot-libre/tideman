<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\CandidateComparator;
use PHPUnit\Framework\TestCase;
use \InvalidArgumentException;

abstract class CandidateComparatorTest extends TestCase
{
    protected const ALICE_ID = "A";
    protected const ALICE_NAME = "Alice";
    protected const BOB_ID = "B";
    protected const BOB_NAME = "Bob";
    protected const CLAIRE_ID = "C";
    protected const CLAIRE_NAME = "Claire";
    protected $alice;
    protected $bob;
    protected $claire;

    //The non-abstract type of CandidateComparator to test
    protected $concreteType;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
    }
    public function testGetWinnerAliceAndLoserBob() : void
    {
        $instance = new $this->concreteType(
            new Ballot(
                new CandidateList($this->alice),
                new CandidateList($this->bob)
            )
        );
        $this->assertEquals(-1, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(1, $instance->compare($this->bob, $this->alice));
    }
    public function testGetWinnerBobAndLoserAlice() : void
    {
        $instance = new $this->concreteType(
            new Ballot(
                new CandidateList($this->bob),
                new CandidateList($this->alice)
            )
        );
        $this->assertEquals(-1, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(1, $instance->compare($this->alice, $this->bob));
    }
    public function testCompareTwoTiedCandidates() : void
    {
        $instance = new $this->concreteType(
            new Ballot(
                new CandidateList($this->alice, $this->bob)
            )
        );
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
    }
    public function testCompareMixedTiedAndNotTiedBallot() : void
    {
        $instance = new $this->concreteType(
            new Ballot(
                new CandidateList($this->alice, $this->bob),
                new CandidateList($this->claire)
            )
        );

        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(-1, $instance->compare($this->alice, $this->claire));
        $this->assertEquals(-1, $instance->compare($this->bob, $this->claire));
        $this->assertEquals(1, $instance->compare($this->claire, $this->alice));
        $this->assertEquals(1, $instance->compare($this->claire, $this->bob));
    }
    public function testCompareMixedNotTiedAndTiedBallot() : void
    {
        $instance = new $this->concreteType(
            new Ballot(
                new CandidateList($this->claire),
                new CandidateList($this->alice, $this->bob)
            )
        );

        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(1, $instance->compare($this->alice, $this->claire));
        $this->assertEquals(1, $instance->compare($this->bob, $this->claire));
        $this->assertEquals(-1, $instance->compare($this->claire, $this->alice));
        $this->assertEquals(-1, $instance->compare($this->claire, $this->bob));
    }
    public function testconstructionFailsOnBallotWithDuplicates() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new $this->concreteType(
            new Ballot(
                new CandidateList($this->alice, $this->alice)
            )
        );
    }
}
