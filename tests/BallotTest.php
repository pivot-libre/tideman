<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class BallotTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CHERYL_ID = "C";
    private const CHERYL_NAME = "Cheryl";
    private const DARIUS_ID = "D";
    private const DARIUS_NAME = "Darius";

    private $alice;
    private $bob;

    protected function setUpValues() : void
    {
        $alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $oneCandidateList = new CandidateList($alice, $bob);
        $darius = new Candidate(self::DARIUS_ID, self::DARIUS_NAME);
        $anotherCandidateList = new CandidateList($darius);
        $this->values = array($oneCandidateList, $anotherCandidateList);
    }
    protected function setUp()
    {
        $this->setUpValues();
        $this->instance = new Ballot(...$this->values);
        $this->concreteType = Ballot::class;
    }
}
