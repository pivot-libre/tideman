<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\PairList;
use PivotLibre\Tideman\ListOfPairLists;
use PivotLibre\Tideman\TieBreaking\TieBreakingMarginComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotPairTieBreaker;
use \InvalidArgumentException;

class TieBreakingMarginComparatorTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private const DAVE_ID = "D";
    private const DAVE_NAME = "David";
    private $alice;
    private $bob;
    private $claire;
    private $dave;
    private $instance;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->dave = new Candidate(self::DAVE_ID, self::DAVE_NAME);
        $tieBreakingBallot = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire),
            new CandidateList($this->dave)
        );
        $candidateComparator = new CandidateComparator($tieBreakingBallot);
        $tieBreaker = new TotallyOrderedBallotPairTieBreaker($candidateComparator);
        $this->instance = new TieBreakingMarginComparator($tieBreaker);

        $loggerFactory = new LoggerFactory();
        $loggerFactory($this->instance);
    }

    public function testNonTiedComparison() : void
    {
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->dave, $this->claire, 5);
        $this->assertLessThan(0, $this->instance->compare($marginA, $marginB));
        $this->assertGreaterThan(0, $this->instance->compare($marginB, $marginA));
    }

    public function testTiedMarginsWithDifferentWinners() : void
    {
        //in these test margins, the differences are the same and the winners are different Candidates
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->dave, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->compare($marginA, $marginB));
        $this->assertGreaterThan(0, $this->instance->compare($marginB, $marginA));
    }

    public function testTiedMarginsWithTheSameWinners() : void
    {
        //in these test margins, the differences are the same and the winners are the same Candidate
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->alice, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->compare($marginA, $marginB));
        $this->assertGreaterThan(0, $this->instance->compare($marginB, $marginA));
    }

    public function testSimpleDifferentWinnerSort() : void
    {
        //in these test margins, the differences are the same and the winners are different Candidates
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->dave, $this->claire, 10);
        $margins = [$marginB, $marginA];
        $expected = [$marginA, $marginB];
        $actual = usort($margins, $this->instance);
        $this->assertEquals($expected, $margins);
    }

    public function testSimpleSameWinnerSort() : void
    {
        //in these test margins, the differences are the same and the winners are the same Candidate
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->alice, $this->claire, 10);
        $margins = [$marginB, $marginA];
        $expected = [$marginA, $marginB];
        $actual = usort($margins, $this->instance);
        $this->assertEquals($expected, $margins);
    }

    public function testBiggerDifferentWinnerSort() : void
    {
        //in these test margins, the differences are the same and the winners are different Candidates
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->bob, $this->claire, 10);
        $marginC = new Pair($this->claire, $this->dave, 10);
        $margins = [$marginB, $marginC, $marginA];
        $expected = [$marginA, $marginB, $marginC];
        $actual = usort($margins, $this->instance);
        $this->assertEquals($expected, $margins);
    }

    public function testBiggerSameWinnerSort() : void
    {
        //in these test margins, the differences are the same and the winners are the same Candidate
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->alice, $this->claire, 10);
        $marginC = new Pair($this->alice, $this->dave, 10);
        $margins = [$marginB, $marginC, $marginA];
        $expected = [$marginA, $marginB, $marginC];
        $actual = usort($margins, $this->instance);
        $this->assertEquals($expected, $margins);
    }

    public function testBiggestSort() : void
    {
        //in these test margins, the differences are the same and the winners are a mix of various Candidates

        //alice as the winner
        $marginA = new Pair($this->alice, $this->bob, 10);
        $marginB = new Pair($this->alice, $this->claire, 10);
        $marginC = new Pair($this->alice, $this->dave, 10);

        //bob as the winner
        $marginD = new Pair($this->bob, $this->claire, 10);
        $marginE = new Pair($this->bob, $this->dave, 10);

        //claire as the winner
        $marginF = new Pair($this->claire, $this->dave, 10);

        $margins = [$marginB, $marginC, $marginA, $marginE, $marginF, $marginD];
        usort($margins, $this->instance);

        $expected = [$marginA, $marginB, $marginC, $marginD, $marginE, $marginF];

        $this->assertEquals($expected, $margins);
    }
}
