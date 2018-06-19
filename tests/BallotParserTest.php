<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class BallotParserTest extends TestCase
{
    private $instance;
    private $alice;
    private $bob;
    private $claire;

    public function setUp() : void
    {
        $this->instance = new BallotParser();
        $this->alice = new Candidate("A");
        $this->bob = new Candidate("B");
        $this->claire = new Candidate("C");
    }

    public function testParseEmptyString() : void
    {
        $expected = new NBallot(1);
        $actual = $this->instance->parse("");
        $this->assertEquals($expected, $actual);
    }

    public function testParseEasyBallot() : void
    {
        $expected = new NBallot(
            1,
            new CandidateList($this->alice),
            new CandidateList($this->bob)
        );
        $actual = $this->instance->parse("A>B");
        $this->assertEquals($expected, $actual);

        //ensure spaces are ignored
        $actual = $this->instance->parse("A >B");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse("A > B");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A  >   B    ");
        $this->assertEquals($expected, $actual);
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
        $actual = $this->instance->parse("A=B");
        $this->assertEquals($aTiedWithB, $actual);
        $actual = $this->instance->parse("B=A");
        $this->assertEquals($bTiedWithA, $actual);


        //ensure spaces are ignored
        $actual = $this->instance->parse("A =B");
        $this->assertEquals($aTiedWithB, $actual);
        $actual = $this->instance->parse("B =A");
        $this->assertEquals($bTiedWithA, $actual);

        $actual = $this->instance->parse("A = B");
        $this->assertEquals($aTiedWithB, $actual);
        $actual = $this->instance->parse("B = A");
        $this->assertEquals($bTiedWithA, $actual);

        $actual = $this->instance->parse(" A  =   B    ");
        $this->assertEquals($aTiedWithB, $actual);
        $actual = $this->instance->parse("    B   =  A ");
        $this->assertEquals($bTiedWithA, $actual);
    }
}
