<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Pair;
use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\TieBreaking\PairTieBreaker;
use PivotLibre\Tideman\ListOfPairLists;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreaking\TieBreakingPairComparator;
use PivotLibre\Tideman\TieBreaking\TotallyOrderedBallotPairTieBreaker;

class ListOfPairListsTest extends GenericCollectionTestCase
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

    private $pairsWithTiedVotess;
    private $pairsWithoutTiedVotess;
    private $tieBreakingPairComparator;

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

        $candidateComparator = new StrictCandidateComparator($tieBreakingBallot);
        $tieBreaker = new TotallyOrderedBallotPairTieBreaker($candidateComparator);
        $this->tieBreakingPairComparator = new TieBreakingPairComparator($tieBreaker);

        $this->pairsWithTiedVotess = new PairList(
            new Pair($this->alice, $this->bob, 1),
            new Pair($this->alice, $this->claire, 1)
        );
        $this->pairsWithoutTiedVotess = new PairList(
            new Pair($this->bob, $this->claire, 5),
            new Pair($this->alice, $this->dave, 4)
        );
        $this->values = [
            $this->pairsWithTiedVotess,
            $this->pairsWithoutTiedVotess
        ];
        $this->instance = new ListOfPairLists(...$this->values);
        $this->concreteType = ListOfPairLists::class;
    }

    public function testNoOpTieBreak() : void
    {
        //empty
        $instance = new ListOfPairLists();
        $actual = $instance->breakTies($this->tieBreakingPairComparator);
        //empty
        $expected = new PairList();

        $this->assertEquals($expected, $actual);
    }

    public function testTieBreakingOnCompletelySortedCandidateList() : void
    {
        $instance = new ListOfPairLists(
            new PairList(
                new Pair($this->bob, $this->claire, 5)
            ),
            new PairList(
                new Pair($this->alice, $this->dave, 4)
            )
        );
        $actual = $instance->breakTies($this->tieBreakingPairComparator);
        $expected = new PairList(
            new Pair($this->bob, $this->claire, 5),
            new Pair($this->alice, $this->dave, 4)
        );

        $this->assertEquals($expected, $actual);
    }
    public function testTieBreakingOnTiedList() : void
    {
        $instance = new ListOfPairLists(
            new PairList(
                new Pair($this->alice, $this->claire, 1),
                new Pair($this->alice, $this->bob, 1)
            )
        );
        $actual = $instance->breakTies($this->tieBreakingPairComparator);
        $expected = new PairList(
            new Pair($this->alice, $this->bob, 1),
            new Pair($this->alice, $this->claire, 1)
        );
        $this->assertEquals($expected, $actual);
    }

    public function testTieBreakingOnMixedList() : void
    {
        $instance = new ListOfPairLists(
            new PairList(
                new Pair($this->alice, $this->dave, 5)
            ),
            new PairList(
                new Pair($this->alice, $this->claire, 3),
                new Pair($this->alice, $this->bob, 3)
            ),
            new PairList(
                new Pair($this->bob, $this->claire, 2)
            )
        );
        $actual = $instance->breakTies($this->tieBreakingPairComparator);
        $expected = new PairList(
            new Pair($this->alice, $this->dave, 5),
            new Pair($this->alice, $this->bob, 3),
            new Pair($this->alice, $this->claire, 3),
            new Pair($this->bob, $this->claire, 2)
        );
        $this->assertEquals($expected, $actual);
    }
}
