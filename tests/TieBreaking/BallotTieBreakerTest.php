<?php
namespace PivotLibre\Tideman\TieBreaking;

use PivotLibre\Tideman\Ballot;
use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\CandidateList;
use PHPUnit\Framework\TestCase;

class BallotTieBreakerTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CHERYL_ID = "C";
    private const CHERYL_NAME = "Cheryl";
    private const DARIUS_ID = "D";
    private const DARIUS_NAME = "Darius";

    protected $alice;
    protected $bob;
    protected $darius;
    protected $tiedCandidateList;
    protected $noTiesCandidateList;
    protected $tiedBallot;
    protected $noTiesBallot;
    protected $instance;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->tiedCandidateList = new CandidateList($this->alice, $this->bob);
        $this->darius = new Candidate(self::DARIUS_ID, self::DARIUS_NAME);
        $this->noTiesCandidateList = new CandidateList($this->darius);
        $this->tiedBallot = new Ballot($this->tiedCandidateList, $this->noTiesCandidateList);
        $this->noTiesBallot = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->darius)
        );
        $this->instance = new BallotTieBreaker();
    }

    public function testTieBreakingOnBallotWithoutTies() : void
    {
        $tieBrokenBallot = $this->instance->breakTiesRandomly($this->noTiesBallot);
        $this->assertNotSame($tieBrokenBallot, $this->noTiesBallot);
        $this->assertEquals($this->noTiesBallot->toArray(), $tieBrokenBallot->toArray());
    }

    public function testTieBreakingOnBallotWithTies() : void
    {
        try {
            //seed the random number generator so that we can reliably test
            mt_srand(4242);
            $tieBrokenBallot = $this->instance->breakTiesRandomly($this->noTiesBallot);
            $this->assertNotSame($tieBrokenBallot, $this->noTiesBallot);
            $this->assertEquals($this->noTiesBallot->toArray(), $tieBrokenBallot->toArray());
        } finally {
            //reset the random number generator
            mt_srand();
        }
    }
}
