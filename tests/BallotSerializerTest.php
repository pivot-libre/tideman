<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class BallotSerializerTest extends TestCase
{
    private $instance;
    private $alice;
    private $bob;
    private $claire;

    public function setUp() : void
    {
        $this->instance = new BallotSerializer();
        $this->alice = new Candidate("A");
        $this->bob = new Candidate("B");
        $this->claire = new Candidate("C");
    }

    public function testOneBallot() : void
    {
        $input = new Ballot(
            new CandidateList($this->alice)
        );
        $actual = $this->instance->serialize($input);
        $this->assertEquals('A', $actual);
    }

    public function testParseEasyBallot() : void
    {
        $input = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob)
        );
        $actual = $this->instance->serialize($input);
        $this->assertEquals("A>B", $actual);
    }

    public function testSimpleBallotWithTie() : void
    {
        //assertion equality is order-dependent, so we create both
        //permutations

        $aTiedWithB = new Ballot(
            //alice and bob are tied
            new CandidateList($this->alice, $this->bob)
        );

        $bTiedWithA = new Ballot(
            //alice and bob are tied
            new CandidateList($this->bob, $this->alice)
        );
        $actual = $this->instance->serialize($aTiedWithB);
        $this->assertEquals("A=B", $actual);
        $actual = $this->instance->serialize($bTiedWithA);
        $this->assertEquals("B=A", $actual);
    }

    public function testThreeBallot() : void
    {
        $input = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        );

     
        $actual = $this->instance->serialize($input);
        $this->assertEquals("A>B=C", $actual);
    }
}
