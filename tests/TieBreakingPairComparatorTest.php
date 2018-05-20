<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\PairList;
use PivotLibre\Tideman\ListOfPairLists;
use PivotLibre\Tideman\TieBreaking\TieBreakingPairComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotPairTieBreaker;
use \InvalidArgumentException;

class TieBreakingPairComparatorTest extends TestCase
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
        $this->instance = new TieBreakingPairComparator($tieBreaker);

        $loggerFactory = new LoggerFactory();
        $loggerFactory($this->instance);
    }

    public function testNonTiedComparison() : void
    {
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->dave, $this->claire, 5);
        $this->assertLessThan(0, $this->instance->compare($pairA, $pairB));
        $this->assertGreaterThan(0, $this->instance->compare($pairB, $pairA));
    }

    public function testTiedPairsWithDifferentWinners() : void
    {
        //in these test pairs, the votes are the same and the winners are different Candidates
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->dave, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->compare($pairA, $pairB));
        $this->assertGreaterThan(0, $this->instance->compare($pairB, $pairA));
    }

    public function testTiedPairsWithTheSameWinners() : void
    {
        //in these test pairs, the votes are the same and the winners are the same Candidate
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->alice, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->compare($pairA, $pairB));
        $this->assertGreaterThan(0, $this->instance->compare($pairB, $pairA));
    }

    public function testSimpleDifferentWinnerSort() : void
    {
        //in these test pairs, the votes are the same and the winners are different Candidates
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->dave, $this->claire, 10);
        $pairs = [$pairB, $pairA];
        $expected = [$pairA, $pairB];
        $actual = usort($pairs, $this->instance);
        $this->assertEquals($expected, $pairs);
    }

    public function testSimpleSameWinnerSort() : void
    {
        //in these test pairs, the votes are the same and the winners are the same Candidate
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->alice, $this->claire, 10);
        $pairs = [$pairB, $pairA];
        $expected = [$pairA, $pairB];
        $actual = usort($pairs, $this->instance);
        $this->assertEquals($expected, $pairs);
    }

    public function testBiggerDifferentWinnerSort() : void
    {
        //in these test pairs, the votes are the same and the winners are different Candidates
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->bob, $this->claire, 10);
        $pairC = new Pair($this->claire, $this->dave, 10);
        $pairs = [$pairB, $pairC, $pairA];
        $expected = [$pairA, $pairB, $pairC];
        $actual = usort($pairs, $this->instance);
        $this->assertEquals($expected, $pairs);
    }

    public function testBiggerSameWinnerSort() : void
    {
        //in these test pairs, the votes are the same and the winners are the same Candidate
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->alice, $this->claire, 10);
        $pairC = new Pair($this->alice, $this->dave, 10);
        $pairs = [$pairB, $pairC, $pairA];
        $expected = [$pairA, $pairB, $pairC];
        $actual = usort($pairs, $this->instance);
        $this->assertEquals($expected, $pairs);
    }

    public function testBiggestSort() : void
    {
        //in these test pairs, the votes are the same and the winners are a mix of various Candidates

        //alice as the winner
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->alice, $this->claire, 10);
        $pairC = new Pair($this->alice, $this->dave, 10);

        //bob as the winner
        $pairD = new Pair($this->bob, $this->claire, 10);
        $pairE = new Pair($this->bob, $this->dave, 10);

        //claire as the winner
        $pairF = new Pair($this->claire, $this->dave, 10);

        $pairs = [$pairB, $pairC, $pairA, $pairE, $pairF, $pairD];
        usort($pairs, $this->instance);

        $expected = [$pairA, $pairB, $pairC, $pairD, $pairE, $pairF];

        $this->assertEquals($expected, $pairs);
    }
}
