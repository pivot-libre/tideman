<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;
use PivotLibre\Tideman\MarginList;

class GrouperTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";

    private $aliceBobMargin;
    private $aliceClaireMargin;
    private $claireBobMargin;

    private $marginList;

    protected function setUp()
    {
        $alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->aliceBobMargin = new Margin($alice, $bob, 1);
        $this->aliceClaireMargin = new Margin($alice, $claire, 1);
        $this->claireBobMargin = new Margin($claire, $bob, 3);
        $this->marginList = new MarginList(
            $this->aliceBobMargin,
            $this->aliceClaireMargin,
            $this->claireBobMargin
        );
    }

    public function testGroupByDifference() : void
    {
        $getDifference = function (Margin $margin) {
            return $margin->getDifference();
        };
        $instance = new Grouper($getDifference);
        $actual = $instance->group($this->marginList);
        $expected = [
            1 => [
                $this->aliceBobMargin,
                $this->aliceClaireMargin
            ],
            3 => [
                $this->claireBobMargin
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGroupBySumOfLengthOfBothCandidatesNames() : void
    {
        $sumLengthOfCandidateNames = function (Margin $margin) {
            $winnerNameLen = strlen($margin->getWinner()->getName());
            $loserNameLen = strlen($margin->getLoser()->getName());
            $combinedLength = $winnerNameLen + $loserNameLen;
            return $combinedLength;
        };
        $instance = new Grouper($sumLengthOfCandidateNames);
        $actual = $instance->group($this->marginList);
        $expected = [
            8 => [
                $this->aliceBobMargin,
            ],
            11 => [
                $this->aliceClaireMargin
            ],
            9 => [
                $this->claireBobMargin
            ]
        ];
        $this->assertEquals($expected, $actual);
    }
}
