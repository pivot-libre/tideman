<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class CandidateTest extends TestCase
{
    private const CANDIDATE_ID = "A";
    private const CANDIDATE_NAME = "Alice";
    private $instance;

    protected function setUp()
    {
        $this->instance = new Candidate(self::CANDIDATE_ID, self::CANDIDATE_NAME);
    }

    public function testGetId()
    {
        $actualCandidateId = $this->instance->getId();
        $this->assertEquals(self::CANDIDATE_ID, $actualCandidateId);
    }

    public function testGetName()
    {
        $actualCandidateName = $this->instance->getName();
        $this->assertEquals(self::CANDIDATE_NAME, $actualCandidateName);
    }
    public function testToString() : void
    {
        $expectedToString = self::CANDIDATE_NAME . "(" . self::CANDIDATE_ID . ")";
        $actualToString = $this->instance->__toString();
        $this->assertEquals($expectedToString, $actualToString);
    }
}
