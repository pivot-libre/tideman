<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\PairList;
use PivotLibre\Tideman\ListOfPairLists;
use \InvalidArgumentException;

class MarginListTest extends GenericCollectionTestCase
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
     * Helper method to ensure that a ListOfMarginLists is correct.
     * See the documentation of MarginList.filterGroupAndSort for details.
     */
    protected function assertGroupedAndInOrderOfDescendingDifference(ListOfPairLists $listofMarginLists)
    {
        $previousMarginGroupDifference = INF;
        $previousMarginDifference = INF;
        foreach ($listofMarginLists as $marginList) {
            //reset each time
            $marginGroupDifference = INF;
            foreach ($marginList as $margin) {
                $currentMarginDifference = $margin->getDifference();
                //if this is the first time through this group
                if (is_infinite($marginGroupDifference)) {
                    //use the difference from the first Margin in the group as the expected difference for the next
                    //Margins in the group
                    $marginGroupDifference = $currentMarginDifference;

                    $this->assertTrue(
                        $marginGroupDifference < $previousMarginGroupDifference,
                        "All Margins of the same difference must be placed in the same group. " .
                        "This Margin was found outside of the group that it should be inside of:" .
                        $margin .
                        "\nThe entire ListOfMarginLists is:" .
                        $listofMarginLists
                    );

                    $previousMarginGroupDifference = $marginGroupDifference;
                } else {
                    //each Margin must have the same difference as other Margins in the same MarginList
                    $this->assertEquals($marginGroupDifference, $currentMarginDifference);
                }
                //each difference should be less than or equal to the previous difference
                $this->assertTrue($previousMarginDifference >= $currentMarginDifference);
                $previousMarginDifference = $currentMarginDifference;
            }
        }
    }
    /**
     * Generates a random MarginList of the specified length with the specified number of ties.
     * Note that you cannot specify a number of ties greater than half of $numMargins because each tie requires two
     * Margins.
     */
    protected function generateMarginList($numMargins, $numberOfTies = 0) : PairList
    {
        $marginsWithoutTies = [];
        $marginsWithTies = [];

        try {
            //seed random number generator to ensure repeatable test results
            srand(31);
            if ($numberOfTies > (int)($numMargins / 2)) {
                throw new InvalidArgumentException("The number of duplicate elements cannot exceed half the list size");
            } else {
                for ($i = 0; $i < $numMargins - $numberOfTies; $i++) {
                    $aCandidate = new Candidate("C#" . $i);
                    $bCandidate = new Candidate("C#" . ($i + $numMargins));
                    $difference = $i;
                    $margin = new Pair($aCandidate, $bCandidate, $difference);
                    $marginsWithoutTies[] = $margin;
                }
                //php copies arrays on assignment
                $marginsWithTies = $marginsWithoutTies;
                //now add ties
                for ($i = $numMargins - $numberOfTies; $i < $numMargins; $i++) {
                    $randomIndex = array_rand($marginsWithoutTies);
                    $marginToDuplicate = $marginsWithoutTies[$randomIndex];
                    $differenceToDuplicate = $marginToDuplicate->getDifference();
                    $aCandidate = new Candidate("C#" . $i);
                    $bCandidate = new Candidate("C#" . ($i + $numMargins));
                    $marginWithDupicateDifference = new Pair($aCandidate, $bCandidate, $differenceToDuplicate);
                    $marginsWithTies[] = $marginWithDupicateDifference;
                }
                shuffle($marginsWithTies);
                return new PairList(...$marginsWithTies);
            }
        } finally {
            //restore random number generator
            srand();
        }
    }
    public function testEmptyList()
    {
        $marginList = new PairList();
        $listOfMarginLists = $marginList->filterGroupAndSort();
        $this->assertEquals(new ListOfPairLists(), $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }

    public function testOneMarginList()
    {
        $marginList = new PairList(new Pair($this->alice, $this->bob, 10));
        $listOfMarginLists = $marginList->filterGroupAndSort();
        $expected = new ListOfPairLists(new PairList(new Pair($this->alice, $this->bob, 10)));
        $this->assertEquals($expected, $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }
    public function testTwoMarginTiedList()
    {
        $marginList = new PairList(
            new Pair($this->alice, $this->bob, 10),
            new Pair($this->alice, $this->claire, 10)
        );
        $listOfMarginLists = $marginList->filterGroupAndSort();
        $expected = new ListOfPairLists(new PairList(
            new Pair($this->alice, $this->bob, 10),
            new Pair($this->alice, $this->claire, 10)
        ));
        $this->assertEquals($expected, $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }
    public function testTwoMarginNonTiedList()
    {
        $marginList = new PairList(
            new Pair($this->alice, $this->claire, 5),
            new Pair($this->alice, $this->bob, 10)
        );

        $listOfMarginLists = $marginList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->alice, $this->bob, 10)
            ),
            new PairList(
                new Pair($this->alice, $this->claire, 5)
            )
        );
        $this->assertEquals($expected, $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }
    public function testThreeMarginsWithNoTies()
    {
        $marginList = new PairList(
            new Pair($this->alice, $this->bob, 10),
            new Pair($this->alice, $this->claire, 2),
            new Pair($this->bob, $this->claire, 300)
        );
        $listOfMarginLists = $marginList->filterGroupAndSort();
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
        $this->assertEquals($expected, $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }
    public function testThreeMarginsWithATieOfStrongerDifference()
    {
        $marginList = new PairList(
            new Pair($this->alice, $this->bob, 100),
            new Pair($this->bob, $this->claire, 10),
            new Pair($this->alice, $this->claire, 100)
        );
        $listOfMarginLists = $marginList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->alice, $this->bob, 100),
                new Pair($this->alice, $this->claire, 100)
            ),
            new PairList(
                new Pair($this->bob, $this->claire, 10)
            )
        );
        $this->assertEquals($expected, $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }
    public function testThreeMarginsWithATieOfWeakerDifference()
    {
        $marginList = new PairList(
            new Pair($this->alice, $this->bob, 2),
            new Pair($this->bob, $this->claire, 100),
            new Pair($this->alice, $this->claire, 2)
        );
        $listOfMarginLists = $marginList->filterGroupAndSort();
        $expected = new ListOfPairLists(
            new PairList(
                new Pair($this->bob, $this->claire, 100)
            ),
            new PairList(
                new Pair($this->alice, $this->bob, 2),
                new Pair($this->alice, $this->claire, 2)
            )
        );
        $this->assertEquals($expected, $listOfMarginLists);
        $this->assertGroupedAndInOrderOfDescendingDifference($listOfMarginLists);
    }
    public function testTenMarginListWithSomeTies()
    {
        $listSize = 10;
        for ($i = 0; $i < $listSize / 2; $i++) {
            $marginList = $this->generateMarginList($listSize, $i);
            $actual = $marginList->filterGroupAndSort();
            $this->assertGroupedAndInOrderOfDescendingDifference($actual);
        }
    }
    public function testThirtyOneMarginListWithSomeTies()
    {
        $listSize = 31;
        for ($i = 0; $i < $listSize / 2; $i++) {
            $marginList = $this->generateMarginList($listSize, $i);
            $actual = $marginList->filterGroupAndSort();
            $this->assertGroupedAndInOrderOfDescendingDifference($actual);
        }
    }
    public function LONGtestOneHundredMarginListWithSomeTies()
    {
        $listSize = 100;
        for ($i = 0; $i < $listSize / 2; $i++) {
            $marginList = $this->generateMarginList($listSize, $i);
            $actual = $marginList->filterGroupAndSort();
            $this->assertGroupedAndInOrderOfDescendingDifference($actual);
        }
    }
}
