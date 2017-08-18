<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;

class ListOfMarginListsTest extends GenericCollectionTestCase
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

        $marginsWithTiedDifferences = new MarginList(
            new Margin($alice, $bob, 1),
            new Margin($alice, $claire, 1)
        );
        $marginWithoutTiedDifferences = new MarginList(
            new Margin($bob, $claire, 5)
        );
        $this->values = [
            $marginsWithTiedDifferences,
            $marginWithoutTiedDifferences
        ];
        $this->instance = new ListOfMarginLists(...$this->values);
        $this->concreteType = ListOfMarginLists::class;
    }
}
