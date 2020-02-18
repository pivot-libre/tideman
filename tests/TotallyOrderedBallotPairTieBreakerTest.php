<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\PairList;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\ListOfPairLists;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreaking\TieBreakingPairComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotPairTieBreaker;
use \InvalidArgumentException;

class TotallyOrderedBallotPairTieBreakerTest extends TestCase
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

        $tieBreakingBallot = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        );
        $candidateComparator = new StrictCandidateComparator($tieBreakingBallot);
        $this->instance = new TotallyOrderedBallotPairTieBreaker($candidateComparator);
    }

    public function testConstructUsingBadTieBreakingBallot() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $badTieBreakingBallot = new Ballot(new CandidateList($this->alice, $this->bob));
        $badComparator = new StrictCandidateComparator($badTieBreakingBallot);
        new TotallyOrderedBallotPairTieBreaker($badComparator);
    }
    public function testNonTiedBallots() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->alice, $this->claire, 0);
        $this->instance->breakTie($pairB, $pairA);
    }
    public function testNonTiedWinners() : void
    {
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->bob, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->breakTie($pairA, $pairB));
        $this->assertGreaterThan(0, $this->instance->breakTie($pairB, $pairA));
    }

    public function testTiedWinners() : void
    {
        $pairA = new Pair($this->alice, $this->bob, 10);
        $pairB = new Pair($this->alice, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->breakTie($pairA, $pairB));
        $this->assertGreaterThan(0, $this->instance->breakTie($pairB, $pairA));
    }
}
