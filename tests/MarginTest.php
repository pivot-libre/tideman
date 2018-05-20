<?php
namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class PairTest extends TestCase
{
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
    }

    public function testGetWinner() : void
    {
        $difference = 0;
        $instance = new Pair($this->alice, $this->bob, $difference);
        $actualWinner = $instance->getWinner();
        $this->assertEquals($this->alice, $actualWinner);
    }

    public function testGetLoser() : void
    {
        $difference = 0;
        $instance = new Pair($this->alice, $this->bob, $difference);
        $actualLoser = $instance->getLoser();
        $this->assertEquals($this->bob, $actualLoser);
    }

    public function testGetPair() : void
    {
        $difference = 42;
        $instance = new Pair($this->alice, $this->bob, $difference);
        $actualPair = $instance->getVotes();
        $this->assertEquals($difference, $actualPair);
    }
    public function testSetPair() : void
    {
        $originalPair = 42;
        $instance = new Pair($this->alice, $this->bob, $originalPair);
        $newPair = 3;
        $instance->setVotes($newPair);
        $actualPair = $instance->getVotes();
        $this->assertEquals($newPair, $actualPair);
    }
    public function testToStringPositiveDifference()
    {
        $difference = 21;
        $instance = new Pair($this->bob, $this->alice, $difference);

        $expectedToString = "(" . $this->bob->getId() . " --(" . $difference . ")--> " . $this->alice->getId() . ")";
        $actualToString = $instance->__toString();
        $this->assertEquals($expectedToString, $actualToString);
    }
    public function testToStringNegativeDifference()
    {
        $difference = -21;
        $instance = new Pair($this->alice, $this->bob, $difference);

        $expectedToString = "(" . $this->alice->getId() . " --(" . $difference . ")--> " . $this->bob->getId() . ")";
        $actualToString = $instance->__toString();
        $this->assertEquals($expectedToString, $actualToString);
    }
}
