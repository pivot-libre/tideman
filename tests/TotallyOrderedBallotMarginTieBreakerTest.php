<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;
use PivotLibre\Tideman\MarginList;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\ListOfMarginLists;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreakingMarginComparator;
use \InvalidArgumentException;

class TotallyOrderedBallotMarginTieBreakerTest extends TestCase
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
        $candidateComparator = new CandidateComparator($tieBreakingBallot);
        $this->instance = new TotallyOrderedBallotMarginTieBreaker($candidateComparator);
    }

    public function testConstructUsingBadTieBreakingBallot() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $badTieBreakingBallot = new Ballot(new CandidateList($this->alice, $this->bob));
        $badComparator = new CandidateComparator($badTieBreakingBallot);
        new TotallyOrderedBallotMarginTieBreaker($badComparator);
    }
    public function testNonTiedBallots() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $marginA = new Margin($this->alice, $this->bob, 10);
        $marginB = new Margin($this->alice, $this->claire, 0);
        $this->instance->breakTie($marginB, $marginA);
    }
    public function testNonTiedWinners() : void
    {
        $marginA = new Margin($this->alice, $this->bob, 10);
        $marginB = new Margin($this->bob, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->breakTie($marginA, $marginB));
        $this->assertGreaterThan(0, $this->instance->breakTie($marginB, $marginA));
    }

    public function testTiedWinners() : void
    {
        $marginA = new Margin($this->alice, $this->bob, 10);
        $marginB = new Margin($this->alice, $this->claire, 10);
        $this->assertLessThan(0, $this->instance->breakTie($marginA, $marginB));
        $this->assertGreaterThan(0, $this->instance->breakTie($marginB, $marginA));
    }
}
