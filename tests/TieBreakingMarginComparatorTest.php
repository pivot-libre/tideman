<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;
use PivotLibre\Tideman\MarginList;
use PivotLibre\Tideman\ListOfMarginLists;
use PivotLibre\Tideman\TieBreakingMarginComparator;
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
        $tieBreaker = new TotallyOrderedBallotMarginTieBreaker($candidateComparator);
        $this->instance = new TieBreakingMarginComparator($tieBreaker);
    }

    public function testBasicCompaison() : void
    {
        $marginA = new Margin($this->alice, $this->bob, 10);
        $marginB = new Margin($this->dave, $this->claire, 5);
        $this->assertGreaterThan(0, $this->instance->compare($marginA, $marginB));
    }
}
