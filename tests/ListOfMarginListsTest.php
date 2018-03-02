<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Margin;
use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\TieBreaking\MarginTieBreaker;
use PivotLibre\Tideman\ListOfMarginLists;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreaking\TieBreakingMarginComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotMarginTieBreaker;

class ListOfMarginListsTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private const DAVE_ID = "D";
    private const DAVE_NAME = "Dave";

    private $alice;
    private $bob;
    private $claire;
    private $dave;

    private $marginsWithTiedDifferences;
    private $marginsWithoutTiedDifferences;
    private $tieBreakingMarginComparator;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->dave = new Candidate(self::DAVE_ID, self::DAVE_NAME);

        $tieBreakingBallot = new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob),
            new CandidateList($this->claire),
            new CandidateList($this->dave)
        );

        $candidateComparator = new CandidateComparator($tieBreakingBallot);
        $tieBreaker = new TotallyOrderedBallotMarginTieBreaker($candidateComparator);
        $this->tieBreakingMarginComparator = new TieBreakingMarginComparator($tieBreaker);

        $this->marginsWithTiedDifferences = new MarginList(
            new Margin($this->alice, $this->bob, 1),
            new Margin($this->alice, $this->claire, 1)
        );
        $this->marginsWithoutTiedDifferences = new MarginList(
            new Margin($this->bob, $this->claire, 5),
            new Margin($this->alice, $this->dave, 4)
        );
        $this->values = [
            $this->marginsWithTiedDifferences,
            $this->marginsWithoutTiedDifferences
        ];
        $this->instance = new ListOfMarginLists(...$this->values);
        $this->concreteType = ListOfMarginLists::class;
    }

    public function testNoOpTieBreak() : void
    {
        //empty
        $instance = new ListOfMarginLists();
        $actual = $instance->breakTies($this->tieBreakingMarginComparator);
        //empty
        $expected = new MarginList();

        $this->assertEquals($expected, $actual);
    }

    public function testTieBreakingOnCompletelySortedCandidateList() : void
    {
        $instance = new ListOfMarginLists(
            new MarginList(
                new Margin($this->bob, $this->claire, 5)
            ),
            new MarginList(
                new Margin($this->alice, $this->dave, 4)
            )
        );
        $actual = $instance->breakTies($this->tieBreakingMarginComparator);
        $expected = new MarginList(
            new Margin($this->bob, $this->claire, 5),
            new Margin($this->alice, $this->dave, 4)
        );

        $this->assertEquals($expected, $actual);
    }
    public function testTieBreakingOnTiedList() : void
    {
        $instance = new ListOfMarginLists(
            new MarginList(
                new Margin($this->alice, $this->claire, 1),
                new Margin($this->alice, $this->bob, 1)
            )
        );
        $actual = $instance->breakTies($this->tieBreakingMarginComparator);
        $expected = new MarginList(
            new Margin($this->alice, $this->bob, 1),
            new Margin($this->alice, $this->claire, 1)
        );
        $this->assertEquals($expected, $actual);
    }

    public function testTieBreakingOnMixedList() : void
    {
        $instance = new ListOfMarginLists(
            new MarginList(
                new Margin($this->alice, $this->dave, 5)
            ),
            new MarginList(
                new Margin($this->alice, $this->claire, 3),
                new Margin($this->alice, $this->bob, 3)
            ),
            new MarginList(
                new Margin($this->bob, $this->claire, 2)
            )
        );
        $actual = $instance->breakTies($this->tieBreakingMarginComparator);
        $expected = new MarginList(
            new Margin($this->alice, $this->dave, 5),
            new Margin($this->alice, $this->bob, 3),
            new Margin($this->alice, $this->claire, 3),
            new Margin($this->bob, $this->claire, 2)
        );
        $this->assertEquals($expected, $actual);
    }
}
