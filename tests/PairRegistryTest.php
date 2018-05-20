<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\PairRegistry;
use PivotLibre\Tideman\Pair;

class PairRegistryTest extends TestCase
{
    private $instance;
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private $alice;
    private $bob;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->instance = new PairRegistry();
    }
    public function testGetFromEmptyRegistry() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->get($this->alice, $this->bob);
    }
    public function testSimpleRegisterAndGet() : void
    {
        $expectedPair = new Pair($this->alice, $this->bob, 42);
        $this->instance->register($expectedPair);

        $actualPair = $this->instance->get($this->alice, $this->bob);

        $this->assertEquals($expectedPair, $actualPair);
        $this->assertEquals($expectedPair->getWinner(), $actualPair->getWinner());
        $this->assertEquals($expectedPair->getLoser(), $actualPair->getLoser());
        $this->assertEquals($expectedPair->getVotes(), $actualPair->getDifference());
    }
    public function testCandidateOrderMatters() : void
    {
        $expectedPairOne = new Pair($this->alice, $this->bob, 42);
        $expectedPairTwo = new Pair($this->bob, $this->alice, -42);
        $this->instance->register($expectedPairOne);
        $this->instance->register($expectedPairTwo);

        $actualPairOne = $this->instance->get($this->alice, $this->bob);
        $this->assertEquals($expectedPairOne, $actualPairOne);
        $this->assertNotEquals($expectedPairTwo, $actualPairOne);
        $actualPairTwo = $this->instance->get($this->bob, $this->alice);
        $this->assertEquals($expectedPairTwo, $actualPairTwo);
        $this->assertNotEquals($expectedPairOne, $actualPairTwo);
    }
    public function testDuplicateRegistrationFails() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $expectedPairOne = new Pair($this->alice, $this->bob, 42);
        $expectedPairTwo = new Pair($this->alice, $this->bob, 42);

        $this->instance->register($expectedPairOne);
        $this->instance->register($expectedPairTwo);
    }
    public function testGetAllWithNoRegisteredPairs() : void
    {
        $pairs = $this->instance->getAll();
        $this->assertEmpty($pairs->toArray());
    }
    public function testGetAllWithOneRegisteredPair() : void
    {
        $pairs = $this->instance->getAll();
        $pair = new Pair($this->alice, $this->bob, 11);
        $this->instance->register($pair);
        $expected = new PairList($pair);
        $actual = $this->instance->getAll();
        $this->assertEquals($expected, $actual);
    }
    public function testGetAllWithTwoRegisteredPairs() : void
    {
        $pairOne = new Pair($this->alice, $this->bob, 11);
        $this->instance->register($pairOne);

        $pairTwo = new Pair($this->bob, $this->alice, -11);
        $this->instance->register($pairTwo);

        $expected = new PairList($pairOne, $pairTwo);
        $actual = $this->instance->getAll();
        //order-inspecific equality test
        $this->assertTrue(
            count($expected->toArray())
            ==
            count(array_intersect($expected->toArray(), $actual->toArray()))
        );
    }

    /**
     * This addresses https://github.com/pivot-libre/tideman/issues/57
     * The PairRegistry didn't put a delimeter between the ids of the winner
     * and the loser candidate when it was building a key for a Pair. This could
     * lead to non-unique keys. When the ids of the winner and loser
     * form a palindrome (for example, 11 and 1) then the PairRegistry
     * class would incorrectly believe that the winner->loser version
     * of a pair was the same as the loser->winner version of the pair.
     *
     * For example, although (1)-->(11) is different than (11)-->(1),
     * the Pair considered them to be the same.
     *
     */
    public function testRegisterPalindromeCandidateIds() : void
    {
        $candidateOne = new Candidate(':', 'One Colon');
        $candidateEleven = new Candidate('::', 'Two Colon');

        $pair = new Pair($candidateOne, $candidateEleven, 0);
        $this->instance->register($pair);

        $oppositePair = new Pair($candidateEleven, $candidateOne, 0);
        $this->instance->register($oppositePair);
        $this->assertEquals(2, $this->instance->getCount());
    }
}
