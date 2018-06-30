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
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
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
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
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
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
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
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
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
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
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
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }

    public function testPairTieBreakingWithNonTiedBallot() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(1, ...$candidateListsForBallot);

        $candidateListsForOppositeBallot = [
            new CandidateList($this->claire),
            new CandidateList($this->bob),
            new CandidateList($this->alice)
        ];
        $oppositeBallot = new NBallot(1, ...$candidateListsForBallot);

        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(3, $ballot, $oppositeBallot);
        $expectedWinners = new CandidateList($this->alice, $this->bob, $this->claire);
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }
    
    public function testPairTieBreakingWitTiedBallot() : void
    {
        $this->markTestSkipped('This test should be restored once candidate ties are surfaced');

        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(1, ...$candidateListsForBallot);
 
        $candidateListsForOppositeBallot = [
            new CandidateList($this->claire),
            new CandidateList($this->bob),
            new CandidateList($this->alice)
        ];
        $oppositeBallot = new NBallot(1, ...$candidateListsForOppositeBallot);

        $tiedTieBreakingCandidateList = new CandidateList(
            $this->claire,
            $this->bob,
            $this->alice
        );
        $tiedTieBreakingBallot = new Ballot($tiedTieBreakingCandidateList);

        //seed the random number generator so that results are consistent across tests
        try {
            mt_srand(485);
            $instance = new RankedPairsCalculator($tiedTieBreakingBallot);
            $actualWinners = $instance->calculate(3, $ballot, $oppositeBallot);
        } finally {
            mt_srand();
        }
        $expectedWinners = new CandidateList($this->alice, $this->claire, $this->bob);
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
        
        //ensure that randomness is affecting the result by re-calculating with a different
        //seed on the random number generator, and asserting different results
        try {
            mt_srand(95);
            $instance = new RankedPairsCalculator($tiedTieBreakingBallot);
            $actualWinners = $instance->calculate(3, $ballot, $oppositeBallot);
        } finally {
            mt_srand();
        }
        $expectedWinners = new CandidateList($this->claire, $this->bob, $this->alice);
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }
}
