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
        $actual = $this->instance->parse("B<A");
        $this->assertEquals($expected, $actual);


        //ensure spaces are ignored
        $actual = $this->instance->parse("A >B");
        $this->assertEquals($expected, $actual);
        $actual = $this->instance->parse("B <A");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse("A > B");
        $this->assertEquals($expected, $actual);
        $actual = $this->instance->parse("B < A");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A  >   B    ");
        $this->assertEquals($expected, $actual);
        $actual = $this->instance->parse("    B   <  A ");
        $this->assertEquals($expected, $actual);
    }

    public function testEnforceOneDirection() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("A<B>C");
    }
}
