<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class AgendaTest extends TestCase
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

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
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
    public function testOneBallotWithoutTies() : void
    {
        $expectedCandidateLists = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new Ballot(...$expectedCandidateLists);
        $instance = new Agenda($ballot);

        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->alice, $this->bob, $this->claire));
    }
    public function testTenBallotsWithoutTies() : void
    {
        $expectedCandidateLists = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new Ballot(...$expectedCandidateLists);
        $ballots = $this->cloneBallot($ballot, 10);
        $instance = new Agenda(...$ballots);
        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->alice, $this->bob, $this->claire));
    }
    public function testOneBallotWithTies() : void
    {
        $expectedCandidateLists = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new Ballot(...$expectedCandidateLists);
        $instance = new Agenda($ballot);

        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->alice, $this->bob, $this->claire));
    }
    public function testTenBallotsWithTies() : void
    {
        $expectedCandidateLists = [
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        ];
        $ballot = new Ballot(...$expectedCandidateLists);
        $ballots = $this->cloneBallot($ballot, 10);
        $instance = new Agenda(...$ballots);
        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->alice, $this->bob, $this->claire));
    }
    public function testRemoveOneCandidate() : void
    {
        $expectedCandidateLists = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new Ballot(...$expectedCandidateLists);
        $instance = new Agenda($ballot);
        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->alice, $this->bob, $this->claire));

        $instance->removeCandidates($this->alice);
        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->bob, $this->claire));
    }
    public function testRemoveMultipleCandidates() : void
    {
        $expectedCandidateLists = [
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        ];
        $ballot = new Ballot(...$expectedCandidateLists);
        $instance = new Agenda($ballot);
        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->alice, $this->bob, $this->claire));

        $instance->removeCandidates($this->alice, $this->claire);
        $this->assertOnlyContainsCandidates($instance->getCandidates(), array($this->bob));
    }
}
