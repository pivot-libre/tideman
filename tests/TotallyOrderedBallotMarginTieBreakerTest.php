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

    private $alice;
    private $bob;
    private $instance;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $tieBreakingBallot = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob)
        );
        $candidateComparator = new CandidateComparator($tieBreakingBallot);
        $tieBreaker = new TotallyOrderedBallotMarginTieBreaker($candidateComparator);
        $this->instance = new TieBreakingMarginComparator($tieBreaker);
    }

    public function testConstructUsingBadTieBreakingBallot() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $badTieBreakingBallot = new Ballot(new CandidateList($this->alice, $this->bob));
        $badComparator = new CandidateComparator($badTieBreakingBallot);
        new TotallyOrderedBallotMarginTieBreaker($badComparator);
    }
}
