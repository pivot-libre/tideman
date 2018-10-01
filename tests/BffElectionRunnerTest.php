<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class BffElectionRunnerTest extends TestCase
{
    private $instance;

    public function setUp() : void
    {
        $this->instance = new BffElectionRunner();
    }

    public function testUnixLikeLineEndings() : void
    {
        $this->instance->setTieBreaker("A>B>C");
        $actual = $this->instance->run("A>B>C\nA>C>B");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testOldMacOsLineEndings() : void
    {
        $this->instance->setTieBreaker("A>B>C");
        $actual = $this->instance->run("A>B>C\rA>C>B");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testWindowsLineEndings() : void
    {
        $this->instance->setTieBreaker("A>B>C");
        $actual = $this->instance->run("A>B>C\r\nA>C>B");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testEmptyLinesIgnored() : void
    {
        $this->instance->setTieBreaker("\n\nA>B>C\n\n");
        $actual = $this->instance->run("\n\nA>B>C\n\nA>C>B\n\n");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testThatLinesWithOnlyWhitespaceAreIgnored() : void
    {
        $this->instance->setTieBreaker("A>B>C");
        $actual = $this->instance->run("\n \nA>B>C\n\t\nA>C>B\n \t \n");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }


    public function testLeadingWhitespaceIgnored() : void
    {
        $this->instance->setTieBreaker(" \t A>B>C");
        $actual = $this->instance->run(" \t A>B>C\n\t\tA>C>B");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testTrailingWhitespaceIgnored() : void
    {
        $this->instance->setTieBreaker("A>B>C \t ");
        $actual = $this->instance->run("A>B>C\t\t\nA>C>B \t ");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testRunAllSimpleElection() : void
    {
        $this->instance->setTieBreaker("A>B>C");
        $actual = $this->instance->runAll('A>B>C', "A>C>B");
        $expected = 'A>B=C';
        $this->assertEquals($expected, $actual);
    }

    public function testRunAllWithNewlines() : void
    {
        $this->expectException(\TypeError::class);
        $this->instance->setTieBreaker("A>B>C \t ");
        $actual = $this->instance->runAll("A>B>C\nA>C>B");
    }
}
