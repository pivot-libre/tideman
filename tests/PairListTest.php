<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\PairList;
use PivotLibre\Tideman\ListOfPairLists;
use \InvalidArgumentException;

class PairListTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private const DAVE_ID = "D";
    private const DAVE_NAME = "David";
    private $alice;
    private $bob;
    private $claire;
    private $dave;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->dave = new Candidate(self::DAVE_ID, self::DAVE_NAME);
        $this->values = array(
            new Pair($this->alice, $this->bob, 1),
            new Pair($this->bob, $this->alice, -1),
            new Pair($this->alice, $this->claire, 10),
            new Pair($this->claire, $this->alice, -10),
            new Pair($this->bob, $this->claire, 5),
            new Pair($this->claire, $this->bob, -5)
        );
        $this->instance = new PairList(...$this->values);
        $this->concreteType = PairList::class;
    }

    /**
     * Helper method to ensure that a ListOfPairLists is correct.
     * See the documentation of PairList.filterGroupAndSort for details.
     */
    protected function assertGroupedAndInOrderOfDescendingDifference(ListOfPairLists $listofPairLists)
    {
        $previousPairGroupDifference = INF;
        $previousPairDifference = INF;
        foreach ($listofPairLists as $pairList) {
            //reset each time
            $pairGroupDifference = INF;
            foreach ($pairList as $pair) {
                $currentPairDifference = $pair->getDifference();
                //if this is the first time through this group
                if (is_infinite($pairGroupDifference)) {
                    //use the difference from the first Pair in the group as the expected difference for the next
                    //Pairs in the group
                    $pairGroupDifference = $currentPairDifference;

                    $this->assertTrue(
                        $pairGroupDifference < $previousPairGroupDifference,
                        "All Pairs of the same difference must be placed in the same group. " .
                        "This Pair was found outside of the group that it should be inside of:" .
                        $pair .
                        "\nThe entire ListOfPairLists is:" .
                        $listofPairLists
                    );

                    $previousPairGroupDifference = $pairGroupDifference;
                } else {
                    //each Pair must have the same difference as other Pairs in the same PairList
                    $this->assertEquals($pairGroupDifference, $currentPairDifference);
                }
                //each difference should be less than or equal to the previous difference
                $this->assertTrue($previousPairDifference >= $currentPairDifference);
                $previousPairDifference = $currentPairDifference;
            }
        }
    }
    /**
     * Generates a random PairList of the specified length with the specified number of ties.
     * Note that you cannot specify a number of ties greater than half of $numPairs because each tie requires two
     * Pairs.
     */
    protected function generatePairList($numPairs, $numberOfTies = 0) : PairList
    {
        $pairsWithoutTies = [];
        $pairsWithTies = [];

        try {
            //seed random number generator to ensure repeatable test results
            srand(31);
            if ($numberOfTies > (int)($numPairs / 2)) {
                throw new InvalidArgumentException("The number of duplicate elements cannot exceed half the list size");
            } else {
                for ($i = 0; $i < $numPairs - $numberOfTies; $i++) {
                    $aCandidate = new Candidate("C#" . $i);
                    $bCandidate = new Candidate("C#" . ($i + $numPairs));
                    $difference = $i;
                    $pair = new Pair($aCandidate, $bCandidate, $difference);
                    $pairsWithoutTies[] = $pair;
                }
                //php copies arrays on assignment
                $pairsWithTies = $pairsWithoutTies;
                //now add ties
                for ($i = $numPairs - $numberOfTies; $i < $numPairs; $i++) {
                    $randomIndex = array_rand($pairsWithoutTies);
                    $pairToDuplicate = $pairsWithoutTies[$randomIndex];
                    $differenceToDuplicate = $pairToDuplicate->getDifference();
                    $aCandidate = new Candidate("C#" . $i);
                    $bCandidate = new Candidate("C#" . ($i + $numPairs));
                    $pairWithDupicateDifference = new Pair($aCandidate, $bCandidate, $differenceToDuplicate);
                    $pairsWithTies[] = $pairWithDupicateDifference;
                }
                shuffle($pairsWithTies);
                return new PairList(...$pairsWithTies);
            }
        } finally {
            //restore random number generator
            srand();
        }
    }
    public function testEmptyList()
    {
        $pairList = new PairList();
        $listOfPairLists = $pairList->filterGroupAndSort();
        $this->assertEquals(new ListOfPairLists(), $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }

    public function testOnePairList()
    {
        $pairList = new PairList(new Pair($this->alice, $this->bob, 10));
        $listOfPairLists = $pairList->filterGroupAndSort();
        $expected = new ListOfPairLists(new PairList(new Pair($this->alice, $this->bob, 10)));
        $this->assertEquals($expected, $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }
    public function testTwoPairTiedList()
    {
        $pairList = new PairList(
            new Pair($this->alice, $this->bob, 10),
            new Pair($this->alice, $this->claire, 10)
        );
        $listOfPairLists = $pairList->filterGroupAndSort();
        $expected = new ListOfPairLists(new PairList(
            new Pair($this->alice, $this->bob, 10),
            new Pair($this->alice, $this->claire, 10)
        ));
        $this->assertEquals($expected, $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }
    public function testTwoPairNonTiedList()
    {
        $pairList = new PairList(
            new Pair($this->alice, $this->claire, 5),
            new Pair($this->alice, $this->bob, 10)
        );

        $listOfPairLists = $pairList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->alice, $this->bob, 10)
            ),
            new PairList(
                new Pair($this->alice, $this->claire, 5)
            )
        );
        $this->assertEquals($expected, $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }
    public function testThreePairsWithNoTies()
    {
        $pairList = new PairList(
            new Pair($this->alice, $this->bob, 10),
            new Pair($this->alice, $this->claire, 2),
            new Pair($this->bob, $this->claire, 300)
        );
        $listOfPairLists = $pairList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->bob, $this->claire, 300)
            ),
            new PairList(
                new Pair($this->alice, $this->bob, 10)
            ),
            new PairList(
                new Pair($this->alice, $this->claire, 2)
            )
        );
        $this->assertEquals($expected, $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }
    public function testThreePairsWithATieOfStrongerDifference()
    {
        $pairList = new PairList(
            new Pair($this->alice, $this->bob, 100),
            new Pair($this->bob, $this->claire, 10),
            new Pair($this->alice, $this->claire, 100)
        );
        $listOfPairLists = $pairList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->alice, $this->bob, 100),
                new Pair($this->alice, $this->claire, 100)
            ),
            new PairList(
                new Pair($this->bob, $this->claire, 10)
            )
        );
        $this->assertEquals($expected, $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }
    public function testThreePairsWithATieOfWeakerDifference()
    {
        $pairList = new PairList(
            new Pair($this->alice, $this->bob, 2),
            new Pair($this->bob, $this->claire, 100),
            new Pair($this->alice, $this->claire, 2)
        );
        $listOfPairLists = $pairList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->bob, $this->claire, 100)
            ),
            new PairList(
                new Pair($this->alice, $this->bob, 2),
                new Pair($this->alice, $this->claire, 2)
            )
        );
        $this->assertEquals($expected, $listOfPairLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfPairLists);
    }
    public function testTenPairListWithSomeTies()
    {
        $listSize = 10;
        for ($i = 0; $i < $listSize / 2; $i++) {
            $pairList = $this->generatePairList($listSize, $i);
            $actual = $pairList->filterGroupAndSort();
            $this->assertGroupedAndInOrderOfDescendingDifference($actual);
        }
    }
    public function testThirtyOnePairListWithSomeTies()
    {
        $listSize = 31;
        for ($i = 0; $i < $listSize / 2; $i++) {
            $pairList = $this->generatePairList($listSize, $i);
            $actual = $pairList->filterGroupAndSort();
            $this->assertGroupedAndInOrderOfDescendingDifference($actual);
        }
    }
    public function LONGtestOneHundredPairListWithSomeTies()
    {
        $listSize = 100;
        for ($i = 0; $i < $listSize / 2; $i++) {
            $pairList = $this->generatePairList($listSize, $i);
            $actual = $pairList->filterGroupAndSort();
            $this->assertGroupedAndInOrderOfDescendingDifference($actual);
        }
    }
}
