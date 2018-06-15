<?php

namespace PivotLibre\Tideman;

use \InvalidArgumentException;
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
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "CLAIRE";

    private $alice;
    private $bob;
    private $claire;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);

        $this->instance = new PairRegistry();
    }

    public function testGetFromEmptyRegistry(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->get($this->alice, $this->bob);
    }

    public function testSimpleRegisterAndGet(): void
    {
        $expectedPair = new Pair($this->alice, $this->bob, 42);
        $this->instance->register($expectedPair);

        $actualPair = $this->instance->get($this->alice, $this->bob);

        $this->assertEquals($expectedPair, $actualPair);
        $this->assertEquals($expectedPair->getWinner(), $actualPair->getWinner());
        $this->assertEquals($expectedPair->getLoser(), $actualPair->getLoser());
        $this->assertEquals($expectedPair->getVotes(), $actualPair->getVotes());
    }

    public function testCandidateOrderMatters(): void
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

    public function testDuplicateRegistrationFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $expectedPairOne = new Pair($this->alice, $this->bob, 42);
        $expectedPairTwo = new Pair($this->alice, $this->bob, 42);

        $this->instance->register($expectedPairOne);
        $this->instance->register($expectedPairTwo);
    }

    public function testGetAllWithNoRegisteredPairs(): void
    {
        $pairs = $this->instance->getAll();
        $this->assertEmpty($pairs->toArray());
    }

    public function testGetAllWithOneRegisteredPair(): void
    {
        $pairs = $this->instance->getAll();
        $pair = new Pair($this->alice, $this->bob, 11);
        $this->instance->register($pair);
        $expected = new PairList($pair);
        $actual = $this->instance->getAll();
        $this->assertEquals($expected, $actual);
    }

    public function testGetAllWithTwoRegisteredPairs(): void
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
    public function testRegisterPalindromeCandidateIds(): void
    {
        $candidateOne = new Candidate(':', 'One Colon');
        $candidateEleven = new Candidate('::', 'Two Colon');

        $pair = new Pair($candidateOne, $candidateEleven, 0);
        $this->instance->register($pair);

        $oppositePair = new Pair($candidateEleven, $candidateOne, 0);
        $this->instance->register($oppositePair);
        $this->assertEquals(2, $this->instance->getCount());
    }

    public function testGetDominatingPairsBeforeRegisteringAnyCandidates(): void
    {
        $actual = $this->instance->getDominatingPairs();
        $this->assertEmpty($actual);
    }

    public function testGetDominatingPairWhenOnlyOnePairIsRegistered(): void
    {
        $pair = new Pair($this->alice, $this->bob, 1);
        $this->instance->register($pair);
        $this->expectException(InvalidArgumentException::class);
        $this->instance->getDominatingPairs();
    }

    public function testGetDominatingPairWhenBothPairsAreRegistered(): void
    {
        $pair = new Pair($this->alice, $this->bob, 1);
        $oppositePair = new Pair($this->bob, $this->alice, -1);

        $expectedPair = clone $pair;
        $expectedPairList = new PairList($expectedPair);

        $this->instance->register($pair);
        $this->instance->register($oppositePair);
        $actualPairList = $this->instance->getDominatingPairs();

        $this->assertEquals($expectedPairList, $actualPairList);
    }

    public function testGetDominatingPairsWhenFourPairsAreRegistered(): void
    {

        $pair1 = new Pair($this->alice, $this->bob, 1);
        $oppositePair1 = new Pair($this->bob, $this->alice, -1);
        $expectedPair1 = clone $pair1;
        $this->instance->register($pair1);
        $this->instance->register($oppositePair1);

        $pair2 = new Pair($this->alice, $this->claire, 1);
        $oppositePair2 = new Pair($this->claire, $this->alice, -1);
        $expectedPair2 = clone $pair2;
        $this->instance->register($pair2);
        $this->instance->register($oppositePair2);

        $expectedPairList = new PairList($expectedPair1, $expectedPair2);
        $actualPairList = $this->instance->getDominatingPairs();

        $this->assertEquals($expectedPairList, $actualPairList);
    }

    public function testGetDominatingPairWithTwoTiedPairs(): void
    {
        $pair = new Pair($this->alice, $this->bob, 1);
        $oppositePair = new Pair($this->bob, $this->alice, 1);

        $expectedPair = clone $pair;
        $expectedPairList = new PairList($expectedPair);

        $this->instance->register($pair);
        $this->instance->register($oppositePair);
        $actualPairList = $this->instance->getDominatingPairs();

        $this->assertEquals($expectedPairList, $actualPairList);
    }

    public function testGetDominatingPairsWithFourTiedPairs(): void
    {

        $pair1 = new Pair($this->alice, $this->bob, 7);
        $oppositePair1 = new Pair($this->bob, $this->alice, 6);

        $expectedPair1 = clone $pair1;
        $this->instance->register($pair1);
        $this->instance->register($oppositePair1);

        $pair2 = new Pair($this->alice, $this->claire, 1);
        $oppositePair2 = new Pair($this->claire, $this->alice, -1);

        $expectedPair2 = clone $pair2;
        $this->instance->register($pair2);
        $this->instance->register($oppositePair2);

        $expectedPairList = new PairList($expectedPair1, $expectedPair2);
        $actualPairList = $this->instance->getDominatingPairs();

        $this->assertEquals($expectedPairList, $actualPairList);
    }

    public function testGetDominatingPairsExcludesZeroVotePairs(): void
    {

        $pair1 = new Pair($this->alice, $this->bob, 42);
        $oppositePair1 = new Pair($this->bob, $this->alice, 2);

        $expectedPair1 = clone $pair1;
        $this->instance->register($pair1);
        $this->instance->register($oppositePair1);

        $pair2 = new Pair($this->alice, $this->claire, 0);
        $oppositePair2 = new Pair($this->claire, $this->alice, 0);

        $this->instance->register($pair2);
        $this->instance->register($oppositePair2);

        $expectedPairList = new PairList($expectedPair1);
        $actualPairList = $this->instance->getDominatingPairs();

        $this->assertEquals($expectedPairList, $actualPairList);
    }
}
