<?php

namespace PivotLibre\Tideman;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\RankedPairsCalculator;

class RankedPairsCalculatorTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private const DAVE_ID = "D";
    private const DAVE_NAME = "Dave";

    private $alice;
    private $bob;
    private $claire;
    private $dave;
    private $tieBreakingBallot;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->dave = new Candidate(self::DAVE_ID, self::DAVE_NAME);

        //construct a ballot that has no ties and that will break ties in alphabetical order
        $this->tieBreakingBallot = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire),
            new CandidateList($this->dave)
        );
    }
    protected function assertOnlyContainsCandidates(CandidateList $actualCandidates, array $expectedCandidates)
    {
        $actualCandidates = $actualCandidates->toArray();
        $this->assertEquals(sizeof($expectedCandidates), sizeof($actualCandidates));
        foreach ($expectedCandidates as $expectedCandidate) {
            $this->assertContains($expectedCandidate, $actualCandidates);
        }
    }
    protected function cloneBallot(Ballot $ballot, int $numBallots) : array
    {
        $ballots = array();
        for ($i = 0; $i < $numBallots; $i++) {
            $ballots[] = clone $ballot;
        }
        return $ballots;
    }


    public function testConstructionSucceedsWhenTieBreakingBallotContainsTies() : void
    {
        $ballotWithTies = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            //the tie
            new CandidateList($this->claire, $this->dave)
        );
        $this->assertNotNull(new RankedPairsCalculator($ballotWithTies));
    }

    public function testConstructionSucceedsWhenTieBreakingBallotContainsNoTies() : void
    {
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $this->assertNotNull($instance);
    }

    public function testOneBallotWithoutTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(1, ...$candidateListsForBallot);
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(sizeof($candidateListsForBallot), ...$ballots);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners);
    }

    public function testOneNBallotWithTenCountWithoutTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(10, ...$candidateListsForBallot);
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(sizeof($candidateListsForBallot), ...$ballots);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners);
    }

    public function testTenBallotsWithoutTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(1, ...$candidateListsForBallot);
        $ballots = $this->cloneBallot($ballot, 10);
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(sizeof($candidateListsForBallot), ...$ballots);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners);
    }

    public function testOneBallotWithTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new NBallot(1, ...$candidateListsForBallot);
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(3, ...$ballots);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners);
    }

    public function testTenBallotsWithTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new NBallot(1, ...$candidateListsForBallot);
        $ballots = $this->cloneBallot($ballot, 10);
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(3, ...$ballots);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners);
    }

    public function testOneNBallotsWithTenCountAndWithTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new NBallot(10, ...$candidateListsForBallot);
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(3, ...$ballots);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners);
    }
}
