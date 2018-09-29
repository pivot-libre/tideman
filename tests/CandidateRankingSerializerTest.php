<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class CandidateRankingSerializerTest extends TestCase
{
    private $instance;
    private $alice;
    private $bob;
    private $claire;
    private $dave;
    private $eve;

    public function setUp() : void
    {
        $this->instance = new CandidateRankingSerializer();
        $this->alice = new Candidate("A");
        $this->bob = new Candidate("B");
        $this->claire = new Candidate("C");
        $this->dave = new Candidate("D");
        $this->eve= new Candidate("E");
    }

    /**
     * Uses relflection to set a candidate's id while bypassing normal candidate id validation rules.
     */
    protected function createCandidateWithAnyId(string $id) : Candidate
    {
        $candidate = new Candidate("tempId");
        $reflection = new \ReflectionClass($candidate);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($candidate, $id);
        $property->setAccessible(false);
        return $candidate;
    }

    public function testSerializeEmptyId() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $candidate = $this->createCandidateWithAnyId('');
        $this->instance->getCandidateId($candidate);
    }

    public function testSerializeOrderedDelim() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $candidate = $this->createCandidateWithAnyId('>');
        $this->instance->getCandidateId($candidate);
    }

    public function testSerializeEqualDelim() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $candidate = $this->createCandidateWithAnyId('=');
        $this->instance->getCandidateId($candidate);
    }

    public function testSerializeLessThan() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $candidate = $this->createCandidateWithAnyId('<');
        $this->instance->getCandidateId($candidate);
    }

    public function testSerializeAsterisk() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $candidate = $this->createCandidateWithAnyId('*');
        $this->instance->getCandidateId($candidate);
    }

    public function testOneCandidateRanking() : void
    {
        $input = new CandidateRanking(
            new CandidateList($this->alice)
        );
        $actual = $this->instance->serialize($input);
        $this->assertEquals('A', $actual);
    }

    public function testParseEasyRanking() : void
    {
        $input = new CandidateRanking(
            new CandidateList($this->alice),
            new CandidateList($this->bob)
        );
        $actual = $this->instance->serialize($input);
        $this->assertEquals("A>B", $actual);
    }

    public function testSimpleRankingWithTie() : void
    {
        //assertion equality is order-dependent, so we create both
        //permutations

        $aTiedWithB = new CandidateRanking(
            //alice and bob are tied
            new CandidateList($this->alice, $this->bob)
        );

        $bTiedWithA = new CandidateRanking(
            //alice and bob are tied
            new CandidateList($this->bob, $this->alice)
        );
        $actual = $this->instance->serialize($aTiedWithB);
        $this->assertEquals("A=B", $actual);
        $actual = $this->instance->serialize($bTiedWithA);
        $this->assertEquals("B=A", $actual);
    }

    public function testThreeCandidateRanking() : void
    {
        $input = new CandidateRanking(
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire)
        );

     
        $actual = $this->instance->serialize($input);
        $this->assertEquals("A>B=C", $actual);
    }

    public function testFourCandidateRanking() : void
    {
        $input = new CandidateRanking(
            new CandidateList($this->alice),
            new CandidateList($this->bob, $this->claire),
            new CandidateList($this->dave)
        );

        $actual = $this->instance->serialize($input);
        $this->assertEquals("A>B=C>D", $actual);
    }

    public function testRankingWithThreeAdjacentTotallyOrderedCandidates() : void
    {
        $input = new CandidateRanking(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire),
            new CandidateList($this->dave, $this->eve)
        );

        $actual = $this->instance->serialize($input);
        $this->assertEquals("A>B>C>D=E", $actual);
    }

    public function testRankingWithThreeAdjacentTiedCandidates() : void
    {
        $input = new CandidateRanking(
            new CandidateList(
                $this->alice,
                $this->bob,
                $this->claire,
                $this->dave
            ),
            new CandidateList($this->eve)
        );

        $actual = $this->instance->serialize($input);
        $this->assertEquals("A=B=C=D>E", $actual);
    }
}
