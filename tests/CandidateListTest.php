<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class CandidateListTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";

    protected function setUp()
    {
        $alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->values = array($alice, $bob);
        $this->instance = new CandidateList(...$this->values);
        $this->concreteType = CandidateList::class;
    }
}
