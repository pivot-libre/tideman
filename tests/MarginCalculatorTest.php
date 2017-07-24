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
    public function testGetWinnerAndLoserWithMissingOuterCandidateId() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $missingIdCandidate = new Candidate('', 'Claire');
        $this->instance->getWinnerAndLoser($missingIdCandidate, $this->bob, []);
    }
    public function testGetWinnerAndLoserWithMissingInnerCandidateId() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $missingIdCandidate = new Candidate('', 'Claire');
        $this->instance->getWinnerAndLoser($this->alice, $missingIdCandidate, []);
    }
}
