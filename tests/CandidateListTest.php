<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class CandidateListTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private $alice;
    private $bob;
    private $values;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->values = array($this->alice, $this->bob);
    }

    public function testValidConstructorCall() : void
    {
        $instance = new CandidateList(...$this->values);
        $this->assertEquals(count($this->values), count($instance->toArray()));
        $this->assertEquals($this->values, $instance->toArray());
    }

    public function testCandidateListReturnsCopyOfArray() : void
    {
        $instance = new CandidateList(...$this->values);
        $this->assertCollectionReturnsCopyOfArray($instance, $this->values);
    }
    public function testCollectionPreservesOriginalOrderAndValues() : void
    {
        $instance = new CandidateList(...$this->values);
        $this->assertCollectionPreservesOriginalOrderAndValues($instance, $this->values);
    }
}
