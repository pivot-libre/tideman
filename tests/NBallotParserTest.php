<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class NBallotParserTest extends TestCase
{
    private $instance;
    private $alice;
    private $bob;
    private $claire;
    private $dave;
    private $eve;

    public function setUp() : void
    {
        $this->instance = new NBallotParser();
        $this->alice = new Candidate("A");
        $this->bob = new Candidate("B");
        $this->claire = new Candidate("C");
        $this->dave = new Candidate("D");
        $this->eve= new Candidate("E");
    }

    public function testParseEmptyString() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $actual = $this->instance->parse("");
    }

    public function testOneCandidateBallot() : void
    {
        $expected = new NBallot(
            1,
            new CandidateList($this->alice)
        );
        $actual = $this->instance->parse("A");
        $this->assertEquals($expected, $actual);

        //add some spaces
        $actual = $this->instance->parse(" A");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A ");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse("A ");
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

        $aTiedWithB = new NBallot(
            1,
            //alice and bob are tied
            new CandidateList($this->alice, $this->bob)
        );

        $bTiedWithA = new NBallot(
            1,
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

    public function testThreeCandidateBallot() : void
    {
        $expected = new NBallot(
            1,
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        );

        $actual = $this->instance->parse("A>B=C");
        $this->assertEquals($expected, $actual);

        //add some spaces
        $actual = $this->instance->parse(" A>B=C");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A> B  =C");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A  >   B    =     C      ");
        $this->assertEquals($expected, $actual);
    }

    public function testFourCandidateBallot() : void
    {
        $expected = new NBallot(
            1,
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire),
            new CandidateList($this->dave)
        );

        $actual = $this->instance->parse("A>B=C>D");
        $this->assertEquals($expected, $actual);

        //add some spaces
        $actual = $this->instance->parse(" A>B=C>D");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A> B  =C>D");
        $this->assertEquals($expected, $actual);

        $actual = $this->instance->parse(" A  >   B    =     C      >       D         ");
        $this->assertEquals($expected, $actual);
    }

    public function testMissingTrailingCandidate() :void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("A>");
    }

    public function testMissingLeadingCandidate() :void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse(">A");
    }

    public function testMissingAnyCandidate() :void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse(">");
    }

    public function testMissingMiddleCandidate() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("A>>B");
    }

    public function testMissingLeadingCandidateInTie() :void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("=A");
    }

    public function testMissingTrailingCandidateInTie() :void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("A=");
    }

    public function testMissingTiedCandidate() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->parse("A==B");
    }

    public function testBallotWithThreeAdjacentTotallyOrderedCandidates() : void
    {
        $expected = new NBallot(
            1,
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire),
            new CandidateList($this->dave, $this->eve)
        );

        $actual = $this->instance->parse("A>B>C>D=E");
        $this->assertEquals($expected, $actual);
    }

    public function testBallotWithThreeAdjacentTiedCandidates() : void
    {
        $expected = new NBallot(
            1,
            new CandidateList(
                $this->alice,
                $this->bob,
                $this->claire,
                $this->dave
            ),
            new CandidateList($this->eve)
        );

        $actual = $this->instance->parse("A=B=C=D>E");
        $this->assertEquals($expected, $actual);
    }
}
