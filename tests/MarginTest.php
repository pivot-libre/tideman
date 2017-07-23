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
        $margin = 0;
        $Margin = new Margin($this->alice, $this->bob, $margin);
        $actualWinner = $Margin->getWinner();
        $this->assertEquals($this->alice, $actualWinner);
    }

    public function testGetLoser() : void
    {
        $margin = 0;
        $Margin = new Margin($this->alice, $this->bob, $margin);
        $actualLoser = $Margin->getLoser();
        $this->assertEquals($this->bob, $actualLoser);
    }

    public function testGetMargin() : void
    {
        $margin = 42;
        $Margin = new Margin($this->alice, $this->bob, $margin);
        $actualMargin = $Margin->getMargin();
        $this->assertEquals($margin, $actualMargin);
    }
    public function testSetMargin() : void
    {
        $originalMargin = 42;
        $margin = new Margin($this->alice, $this->bob, $originalMargin);
        $newMargin = 3;
        $margin->setMargin($newMargin);
        $actualMargin = $margin->getMargin();
        $this->assertEquals($newMargin, $actualMargin);
    }
    public function testToString()
    {
        $margin = 21;
        $Margin = new Margin($this->bob, $this->alice, $margin);

        $expectedToString = $this->bob->getId() . " --" . $margin . "--> " . $this->alice->getId();
        $actualToString = $Margin->__toString();
        $this->assertEquals($expectedToString, $actualToString);
    }
}
