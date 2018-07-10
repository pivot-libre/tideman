<?php

namespace PivotLibre\Tideman;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\RankedPairsCalculator;

class RankedPairsCalculatorTest extends TestCase
{
    private const ALICE_ID = "A";
    private const BOB_ID = "B";
    private const CLAIRE_ID = "C";
    private const DAVE_ID = "D";

    private $alice;
    private $bob;
    private $claire;
    private $dave;
    private $tieBreakingBallot;
    private $parser;

    protected function setUp()
    {
        $this->parser = new CandidateRankingParser();
        $this->alice = new Candidate(self::ALICE_ID);
        $this->bob = new Candidate(self::BOB_ID);
        $this->claire = new Candidate(self::CLAIRE_ID);
        $this->dave = new Candidate(self::DAVE_ID);

        //construct a ballot that has no ties and that will break ties in alphabetical order
        $this->tieBreakingBallot = new Ballot(
            ...$this->parser->parse("A>B>C>D")
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
            ...$this->parser->parse("A>B>C=D")
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
        $ballot = new NBallot(
            1,
            ...$this->parser->parse(
                "A>B>C"
            )
        );
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(sizeof($candidateListsForBallot), ...$ballots);
        $expectedWinners = $this->parser->parse("A>B>C");
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }

    public function testOneNBallotWithTenCountWithoutTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(10, ...$this->parser->parse(
            "A>B>C"
        ));
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(sizeof($candidateListsForBallot), ...$ballots);
        $expectedWinners = $this->parser->parse("A>B>C");
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }

    public function testTenBallotsWithoutTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new NBallot(1, ...$this->parser->parse(
            "A>B>C"
        ));
        $ballots = $this->cloneBallot($ballot, 10);
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(sizeof($candidateListsForBallot), ...$ballots);
        $expectedWinners = $this->parser->parse("A>B>C");
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }

    public function testOneBallotWithTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new NBallot(1, ...$this->parser->parse("A>B=C"));
        $ballots = [$ballot];
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(3, ...$ballots);
        $expectedWinners = $this->parser->parse("A>B=C");
        $this->assertEquals($expectedWinners, $actualWinners->getRanking());
    }

    public function testTenBallotsWithTies() : void
    {
        $candidateListsForBallot = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new NBallot(1, ...$this->parser->parse(
            "A>B=C"
        ));
        $ballots = $this->cloneBallot($ballot, 10);
        $instance = new RankedPairsCalculator($this->tieBreakingBallot);
        $actualWinners = $instance->calculate(3, ...$ballots);
        $expectedWinners = $this->parser->parse("A>B=C");
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
        $expectedWinners = $this->parser->parse("A>B=C");
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
        $expectedWinners = $this->parser->parse("A>B>C");
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

    public function testTideman1987Example5()
    {
        $expectedRanking = (new CandidateRankingParser())->parse("V>W>X>Y>Z");

        $ballots = (new TestScenarioTideman1987Example2())->getBallots();
        $tieBreakingBallot = $ballots[0];

        $instance = new RankedPairsCalculator($tieBreakingBallot);
        $results = $instance->calculate(sizeof($ballots), ...$ballots);
        $actualRanking = $results->getRanking();

        $this->assertEquals($expectedRanking, $actualRanking);
    }

    /**
     * Scenario 1 from the test spreadsheet
     * https://docs.google.com/spreadsheets/d/1634wP6-N8GG2Fig-yjIOk7vPBn4AijXOrjq6Z2T1K8M/edit?usp=sharing
     * Drawing of graph:
     * https://docs.google.com/drawings/d/1mtGlWgqr_h85qdvqSjC9eK0bRzbGYas-sLhuzAiDd3I/edit?usp=sharing
     */
    public function testScenario1()
    {
        $expectedRanking = (new CandidateRankingParser())->parse("MM>SY=DD>YW>RR");

        $ballots = (new TestScenario1())->getBallots();
        $tieBreakingBallot = $ballots[0];

        $instance = new RankedPairsCalculator($tieBreakingBallot);
        $results = $instance->calculate(sizeof($ballots), ...$ballots);
        $actualRanking = $results->getRanking();

        $this->assertEquals($expectedRanking, $actualRanking);
    }

    /**
     * Scenario 2 from the test spreadsheet
     * https://docs.google.com/spreadsheets/d/1634wP6-N8GG2Fig-yjIOk7vPBn4AijXOrjq6Z2T1K8M/edit?usp=sharing
     */
    public function testScenario2()
    {
        $expectedRanking = (new CandidateRankingParser())->parse("MM>BT>FE=CS>RR");

        $ballots = (new TestScenario2())->getBallots();
        $tieBreakingBallot = $ballots[0];

        $instance = new RankedPairsCalculator($tieBreakingBallot);
        $results = $instance->calculate(sizeof($ballots), ...$ballots);
        $actualRanking = $results->getRanking();

        $this->assertEquals($expectedRanking, $actualRanking);
    }

}
