<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class NBallotParserTest extends TestCase
{
    private $instance;
    private $alice;
    private $bob;
    private $claire;

    public function setUp()
    {
        $this->instance = new NBallotParser();
        $this->alice = new Candidate("A");
        $this->bob = new Candidate("B");
        $this->claire = new Candidate("C");
    }

    public function testGoodText()
    {
        $expected = new NBallot(
            3,
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        );

        $actual = $this->instance->parse("3*A>B>C");
        $this->assertEquals($expected, $actual);

        //try with various spaces
        $actual = $this->instance->parse(" 3*A>B>C");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse("3 *A>B>C");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse("3* A>B>C");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse("3 * A>B>C");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" 3 * A>B>C");
        $this->assertEquals($expected, $actual);
    }

    public function testThatMultiplierIsOptional()
    {
        $expected = new NBallot(
            1,
            new CandidateList($this->alice),
            new CandidateList($this->claire),
            new CandidateList($this->bob)
        );

        $actual = $this->instance->parse("A>C>B");
        $this->assertEquals($expected, $actual);
    }

    public function testThatZeroBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("0*A>B>C");
    }

    public function testThatNegativeZeroBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("-0*A>B>C");
    }

    public function testThatPositiveZeroBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("+0*A>B>C");
    }

    public function testThatNegativeOneBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("-1*A>B>C");
    }

    public function testThatNegativeIntegersBreak()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("-3*A>B>C");
    }

    public function testThatPositiveFloatNumberBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("3.14159*A>B>C");
    }

    public function testThatNegativeFloatNumberBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("-3.14159*A>B>C");
    }

    public function testDoubleMultiplierBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("2*2*A>B>C");
    }

    public function testDisconnectedMultiplierBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("2*A>B>C*2");
    }

    public function testTrailingMultiplierBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("A>B>C*2");
    }

    public function testBlankBreaks()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("");
    }
}
