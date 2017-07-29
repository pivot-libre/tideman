<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\CandidateComparator;
use PHPUnit\Framework\TestCase;
use \InvalidArgumentException;

class CandidateComparatorTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private $alice;
    private $bob;
    private $claire;
    private $instance;
    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
    }
    public function testGetWinnerAliceAndLoserBob() : void
    {
        $instance = new CandidateComparator(
            new Ballot(
                new CandidateList($this->alice),
                new CandidateList($this->bob)
            )
        );
        $this->assertEquals(
            array(
                self::ALICE_ID => 0,
                self::BOB_ID => 1
            ),
            $instance->getCandidateIdToRankMap()
        );
        $this->assertEquals(1, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(-1, $instance->compare($this->bob, $this->alice));
    }
    public function testGetWinnerBobAndLoserAlice() : void
    {
        $instance = new CandidateComparator(
            new Ballot(
                new CandidateList($this->bob),
                new CandidateList($this->alice)
            )
        );
        $this->assertEquals(
            array(
                self::ALICE_ID => 1,
                self::BOB_ID => 0
            ),
            $instance->getCandidateIdToRankMap()
        );
        $this->assertEquals(1, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(-1, $instance->compare($this->alice, $this->bob));
    }
    public function testGetCandidateIdToRankMapFromEmptyBallot() : void
    {
        $instance = new CandidateComparator(new Ballot());
        $map = $instance->getCandidateIdToRankMap();
        $this->assertEmpty($map);
        $this->expectException(InvalidArgumentException::class);
        $instance->compare($this->alice, $this->bob);
    }
    public function testGetCandidateIdToRankMapFromOneCandidateBallot() : void
    {
        $instance = new CandidateComparator(new Ballot(
            new CandidateList(
                $this->alice
            )
        ));
        $map = $instance->getCandidateIdToRankMap();
        $this->assertEquals(
            array(
                self::ALICE_ID => 0
            ),
            $map
        );
        $this->expectException(InvalidArgumentException::class);
        $instance->compare($this->alice, $this->bob);
    }
    // public function testGetCandidateIdToRankMapFromTwoCandidateBallot() : void
    // {
    //     $map = $this->instance->getCandidateIdToRankMap(new Ballot(
    //         new CandidateList($this->alice),
    //         new CandidateList($this->bob)
    //     ));
    //     $this->assertSame(0, $map[self::ALICE_ID]);
    //     $this->assertSame(1, $map[self::BOB_ID]);
    // }
    public function testGetCandidateIdToRankMapFromTwoTiedCandidateBallot() : void
    {
        $instance = new CandidateComparator(
            new Ballot(
                new CandidateList($this->alice, $this->bob)
            )
        );
        $this->assertEquals(
            array(
                self::ALICE_ID => 0,
                self::BOB_ID => 0
            ),
            $instance->getCandidateIdToRankMap()
        );
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
    }
    public function testGetCandidateIdToRankMapFromMixedTiedAndNotTiedBallot() : void
    {
        $instance = new CandidateComparator(
            new Ballot(
                new CandidateList($this->alice, $this->bob),
                new CandidateList($this->claire)
            )
        );
        $this->assertEquals(
            array(
                self::ALICE_ID => 0,
                self::BOB_ID => 0,
                self::CLAIRE_ID => 1
            ),
            $instance->getCandidateIdToRankMap()
        );
        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(1, $instance->compare($this->alice, $this->claire));
        $this->assertEquals(1, $instance->compare($this->bob, $this->claire));
        $this->assertEquals(-1, $instance->compare($this->claire, $this->alice));
        $this->assertEquals(-1, $instance->compare($this->claire, $this->bob));
    }
    public function testGetCandidateIdToRankMapFromMixedNotTiedAndTiedBallot() : void
    {
        $instance = new CandidateComparator(
            new Ballot(
                new CandidateList($this->claire),
                new CandidateList($this->alice, $this->bob)
            )
        );
        $this->assertEquals(
            array(
                self::ALICE_ID => 1,
                self::BOB_ID => 1,
                self::CLAIRE_ID => 0
            ),
            $instance->getCandidateIdToRankMap()
        );
        $this->assertEquals(0, $instance->compare($this->alice, $this->bob));
        $this->assertEquals(0, $instance->compare($this->bob, $this->alice));
        $this->assertEquals(-1, $instance->compare($this->alice, $this->claire));
        $this->assertEquals(-1, $instance->compare($this->bob, $this->claire));
        $this->assertEquals(1, $instance->compare($this->claire, $this->alice));
        $this->assertEquals(1, $instance->compare($this->claire, $this->bob));
    }
    public function testGetCandidateIdToRankMapFailsOnBallotWithDuplicates() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new CandidateComparator(
            new Ballot(
                new CandidateList($this->alice, $this->alice)
            )
        );
    }
}
