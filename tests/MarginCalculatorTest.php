<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\MarginCalculator;
use \InvalidArgumentException;

class MarginCalculatorTest extends TestCase
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
        $this->instance = new MarginCalculator();
    }
    public function testGetWinnerAliceAndLoserBob() : void
    {
        $candidateIdToRankMap = [
            self::ALICE_ID => 0,
            self::BOB_ID => 1
        ];

        list($actualWinner, $actualLoser) = $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );

        $this->assertEquals($this->alice, $actualWinner);
        $this->assertEquals($this->bob, $actualLoser);
    }
    public function testGetWinnerBobAndLoserAlice() : void
    {
        $candidateIdToRankMap = [
            self::ALICE_ID => 1,
            self::BOB_ID => 0
        ];

        list($actualWinner, $actualLoser) = $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );

        $this->assertEquals($this->bob, $actualWinner);
        $this->assertEquals($this->alice, $actualLoser);
    }
    public function testGetWinnerAndLoserWithInnerCandidateMissingFromMap() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $candidateIdToRankMap = [
            self::ALICE_ID => 0,
        ];
        $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );
    }
    public function testGetWinnerAndLoserWithOuterCandidateMissingFromMap() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $candidateIdToRankMap = [
            self::BOB_ID => 0,
        ];
        $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );
    }
    public function testGetCandidateIdToRankMapFromEmptyBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot());
        $this->assertEmpty($map);
    }
    public function testGetCandidateIdToRankMapFromOneCandidateBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
    }
    public function testGetCandidateIdToRankMapFromTwoCandidateBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
        $this->assertSame(1, $map[self::BOB_ID]);
    }
    public function testGetCandidateIdToRankMapFromTwoTiedCandidateBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice, $this->bob)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
        $this->assertSame(0, $map[self::BOB_ID]);
    }
    public function testGetCandidateIdToRankMapFromMixedTiedAndNotTiedBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice, $this->bob),
            new CandidateList($this->claire)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
        $this->assertSame(0, $map[self::BOB_ID]);
        $this->assertSame(1, $map[self::CLAIRE_ID]);
    }
    public function testGetCandidateIdToRankMapFromMixedNotTiedAndTiedBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->claire),
            new CandidateList($this->alice, $this->bob)
        ));
        $this->assertSame(0, $map[self::CLAIRE_ID]);
        $this->assertSame(1, $map[self::ALICE_ID]);
        $this->assertSame(1, $map[self::BOB_ID]);
    }
    public function testGetCandidateIdToRankMapFailsOnBallotWithDuplicates() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice, $this->alice)
        ));
    }
}
