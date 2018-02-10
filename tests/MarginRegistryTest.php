<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\MarginRegistry;
use PivotLibre\Tideman\Margin;

class MarginRegistryTest extends TestCase
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
        $this->instance = new MarginRegistry();
    }
    public function testGetFromEmptyRegistry() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->get($this->alice, $this->bob);
    }
    public function testSimpleRegisterAndGet() : void
    {
        $expectedMargin = new Margin($this->alice, $this->bob, 42);
        $this->instance->register($expectedMargin);

        $actualMargin = $this->instance->get($this->alice, $this->bob);

        $this->assertEquals($expectedMargin, $actualMargin);
        $this->assertEquals($expectedMargin->getWinner(), $actualMargin->getWinner());
        $this->assertEquals($expectedMargin->getLoser(), $actualMargin->getLoser());
        $this->assertEquals($expectedMargin->getDifference(), $actualMargin->getDifference());
    }
    public function testCandidateOrderMatters() : void
    {
        $expectedMarginOne = new Margin($this->alice, $this->bob, 42);
        $expectedMarginTwo = new Margin($this->bob, $this->alice, -42);
        $this->instance->register($expectedMarginOne);
        $this->instance->register($expectedMarginTwo);

        $actualMarginOne = $this->instance->get($this->alice, $this->bob);
        $this->assertEquals($expectedMarginOne, $actualMarginOne);
        $this->assertNotEquals($expectedMarginTwo, $actualMarginOne);
        $actualMarginTwo = $this->instance->get($this->bob, $this->alice);
        $this->assertEquals($expectedMarginTwo, $actualMarginTwo);
        $this->assertNotEquals($expectedMarginOne, $actualMarginTwo);
    }
    public function testDuplicateRegistrationFails() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $expectedMarginOne = new Margin($this->alice, $this->bob, 42);
        $expectedMarginTwo = new Margin($this->alice, $this->bob, 42);

        $this->instance->register($expectedMarginOne);
        $this->instance->register($expectedMarginTwo);
    }
    public function testGetAllWithNoRegisteredMargins() : void
    {
        $margins = $this->instance->getAll();
        $this->assertEmpty($margins->toArray());
    }
    public function testGetAllWithOneRegisteredMargin() : void
    {
        $margins = $this->instance->getAll();
        $margin = new Margin($this->alice, $this->bob, 11);
        $this->instance->register($margin);
        $expected = new MarginList($margin);
        $actual = $this->instance->getAll();
        $this->assertEquals($expected, $actual);
    }
    public function testGetAllWithTwoRegisteredMargins() : void
    {
        $marginOne = new Margin($this->alice, $this->bob, 11);
        $this->instance->register($marginOne);

        $marginTwo = new Margin($this->bob, $this->alice, -11);
        $this->instance->register($marginTwo);

        $expected = new MarginList($marginOne, $marginTwo);
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
     * The MarginRegistry didn't put a delimeter between the ids of the winner
     * and the loser candidate when it was building a key for a Margin. This could
     * lead to non-unique keys. When the ids of the winner and loser
     * form a palindrome (for example, 11 and 1) then the MarginRegistry
     * class would incorrectly believe that the winner->loser version
     * of a margin was the same as the loser->winner version of the margin.
     *
     * For example, although (1)-->(11) is different than (11)-->(1),
     * the Margin considered them to be the same.
     *
     */
    public function testRegisterPalindromeCandidateIds() : void
    {
        $candidateOne = new Candidate(':', 'One Colon');
        $candidateEleven = new Candidate('::', 'Two Colon');

        $margin = new Margin($candidateOne, $candidateEleven, 0);
        $this->instance->register($margin);

        $oppositeMargin = new Margin($candidateEleven, $candidateOne, 0);
        $this->instance->register($oppositeMargin);
        $this->assertEquals(2, $this->instance->getCount());
    }
}
