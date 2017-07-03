<?php
namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class MajorityTest extends TestCase
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
        $majority = new Majority($this->alice, $this->bob, $margin);
        $actualWinner = $majority->getWinner();
        $this->assertEquals($this->alice, $actualWinner);
    }

    public function testGetLoser() : void
    {
        $margin = 0;
        $majority = new Majority($this->alice, $this->bob, $margin);
        $actualLoser = $majority->getLoser();
        $this->assertEquals($this->bob, $actualLoser);
    }

    public function testGetMargin() : void
    {
        $margin = 42;
        $majority = new Majority($this->alice, $this->bob, $margin);
        $actualMargin = $majority->getMargin();
        $this->assertEquals($margin, $actualMargin);
    }
}
