<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\PairList;

class GrouperTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";

    private $aliceBobPair;
    private $aliceClairePair;
    private $claireBobPair;

    private $pairList;

    protected function setUp()
    {
        $alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->aliceBobPair = new Pair($alice, $bob, 1);
        $this->aliceClairePair = new Pair($alice, $claire, 1);
        $this->claireBobPair = new Pair($claire, $bob, 3);
        $this->pairList = new PairList(
            $this->aliceBobPair,
            $this->aliceClairePair,
            $this->claireBobPair
        );
    }

    public function testGroupByVotes() : void
    {
        $getVotes = function (Pair $pair) {
            return $pair->getVotes();
        };
        $instance = new Grouper($getVotes);
        $actual = $instance->group($this->pairList);
        $expected = [
            1 => [
                $this->aliceBobPair,
                $this->aliceClairePair
            ],
            3 => [
                $this->claireBobPair
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGroupBySumOfLengthOfBothCandidatesNames() : void
    {
        $sumLengthOfCandidateNames = function (Pair $pair) {
            $winnerNameLen = strlen($pair->getWinner()->getName());
            $loserNameLen = strlen($pair->getLoser()->getName());
            $combinedLength = $winnerNameLen + $loserNameLen;
            return $combinedLength;
        };
        $instance = new Grouper($sumLengthOfCandidateNames);
        $actual = $instance->group($this->pairList);
        $expected = [
            8 => [
                $this->aliceBobPair,
            ],
            11 => [
                $this->aliceClairePair
            ],
            9 => [
                $this->claireBobPair
            ]
        ];
        $this->assertEquals($expected, $actual);
    }
}
