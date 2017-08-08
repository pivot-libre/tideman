<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;

class MarginListTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";

    protected function setUp()
    {
        $alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);

        $this->values = array(
            new Margin($alice, $bob, 1),
            new Margin($bob, $alice, -1),
            new Margin($alice, $claire, 10),
            new Margin($claire, $alice, -10),
            new Margin($bob, $claire, 5),
            new Margin($claire, $bob, -5)
        );
        $this->instance = new MarginList(...$this->values);
        $this->concreteType = MarginList::class;
    }
}
