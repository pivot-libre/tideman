<?php
namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class MarginTest extends TestCase
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
        $instance = new Margin($this->alice, $this->bob, $difference);
        $actualWinner = $instance->getWinner();
        $this->assertEquals($this->alice, $actualWinner);
    }

    public function testGetLoser() : void
    {
        $difference = 0;
        $instance = new Margin($this->alice, $this->bob, $difference);
        $actualLoser = $instance->getLoser();
        $this->assertEquals($this->bob, $actualLoser);
    }

    public function testGetMargin() : void
    {
        $difference = 42;
        $instance = new Margin($this->alice, $this->bob, $difference);
        $actualMargin = $instance->getDifference();
        $this->assertEquals($difference, $actualMargin);
    }
    public function testSetMargin() : void
    {
        $originalMargin = 42;
        $instance = new Margin($this->alice, $this->bob, $originalMargin);
        $newMargin = 3;
        $instance->setDifference($newMargin);
        $actualMargin = $instance->getDifference();
        $this->assertEquals($newMargin, $actualMargin);
    }
    public function testToStringPositiveDifference()
    {
        $difference = 21;
        $instance = new Margin($this->bob, $this->alice, $difference);

        $expectedToString = "(" . $this->bob->getId() . " --(" . $difference . ")--> " . $this->alice->getId() . ")";
        $actualToString = $instance->__toString();
        $this->assertEquals($expectedToString, $actualToString);
    }
    public function testToStringNegativeDifference()
    {
        $difference = -21;
        $instance = new Margin($this->alice, $this->bob, $difference);

        $expectedToString = "(" . $this->alice->getId() . " --(" . $difference . ")--> " . $this->bob->getId() . ")";
        $actualToString = $instance->__toString();
        $this->assertEquals($expectedToString, $actualToString);
    }
}
