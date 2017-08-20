<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;
use PivotLibre\Tideman\MarginList;
use PivotLibre\Tideman\ListOfMarginLists;
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
            new Margin($this->alice, $this->bob, 1),
            new Margin($this->bob, $this->alice, -1),
            new Margin($this->alice, $this->claire, 10),
            new Margin($this->claire, $this->alice, -10),
            new Margin($this->bob, $this->claire, 5),
            new Margin($this->claire, $this->bob, -5)
        );
        $this->instance = new MarginList(...$this->values);
        $this->concreteType = MarginList::class;
    }

    /**
     * Helper method to ensure that a ListOfMarginLists is correct.
     * See the documentation of MarginList.filterGroupAndSort for details.
     */
    protected function assertGroupedAndInOrderOfDescendingDifference(ListOfMarginLists $listofMarginLists)
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
    protected function generateMarginList($numMargins, $numberOfTies = 0) : MarginList
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
                    $margin = new Margin($aCandidate, $bCandidate, $difference);
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
                    $marginWithDupicateDifference = new Margin($aCandidate, $bCandidate, $differenceToDuplicate);
                    $marginsWithTies[] = $marginWithDupicateDifference;
                }
                shuffle($marginsWithTies);
                return new MarginList(...$marginsWithTies);
            }
        } finally {
            //restore random number generator
            srand();
        }
    }
}
