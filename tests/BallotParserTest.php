<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

/**
 * Class BallotParserTest
 * Presently, the BallotParser is just a thin wrapper around the CandidateRankingParser. We can safely perform minimal
 * testing here because most functionality is extensively tested in CandidateRankingParserTest.
 * @package PivotLibre\Tideman
 */
class BallotParserTest extends TestCase
{
    private $instance;
    private $alice;
    private $bob;
    private $claire;

    public function setUp()
    {
        $this->instance = new BallotParser();
        $this->alice = new Candidate("A");
        $this->bob = new Candidate("B");
        $this->claire = new Candidate("C");
    }

    public function testGoodBallot()
    {
        $expected = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire)
        );

        $actual = $this->instance->parse("A>B>C");
        $this->assertEquals($expected, $actual);
    }

    public function testThatAsteriskBreaks()
    {
        //BallotParser should not parse NBallot text
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("1*A>B>C");
    }
}
