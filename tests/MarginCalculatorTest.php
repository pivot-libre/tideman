<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\Agenda;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\MarginCalculator;
use \InvalidArgumentException;

class MarginCalculatorTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private $alice;
    private $bob;
    private $claire;
    private $instance;
    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->instance = new MarginCalculator();
    }

    public function testUpdatePairInRegistry() : void
    {
        $registry = new PairRegistry();
        $registry->register(new Pair($this->alice, $this->bob, 0));
        $this->instance->incrementPairInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            42
        );
        $actualPair = $registry->get($this->alice, $this->bob);
        $this->assertEquals(42, $actualPair->getVotes());
    }
    public function testUpdatePairIgnoreAnotherPairInRegistry() : void
    {
        $registry = new PairRegistry();
        $registry->register(new Pair($this->alice, $this->bob, 0));
        $registry->register(new Pair($this->claire, $this->bob, 0));

        $this->instance->incrementPairInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            5
        );
        $actualPair = $registry->get($this->alice, $this->bob);
        $this->assertEquals(5, $actualPair->getVotes());

        $untouchedPair = $registry->get($this->claire, $this->bob);
        $this->assertEquals(0, $untouchedPair->getVotes());
    }

    public function testTwoUpdatesOfTwoPairsInRegistry() : void
    {
        $registry = new PairRegistry();
        $registry->register(new Pair($this->alice, $this->bob, 0));
        $registry->register(new Pair($this->claire, $this->bob, 0));

        //add 1 to Alice->Bob, don't touch Claire->Bob
        $this->instance->incrementPairInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            1
        );
        $actualPair = $registry->get($this->alice, $this->bob);
        $this->assertEquals(1, $actualPair->getVotes());

        $untouchedPair = $registry->get($this->claire, $this->bob);
        $this->assertEquals(0, $untouchedPair->getVotes());

        //add 17 to Alice->Bob, don't touch Claire->Bob
        $this->instance->incrementPairInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            17
        );
        $actualPair = $registry->get($this->alice, $this->bob);
        $this->assertEquals(18, $actualPair->getVotes());

        $untouchedPair = $registry->get($this->claire, $this->bob);
        $this->assertEquals(0, $untouchedPair->getVotes());

        //Add 3 to Claire->Bob, don't touch Alice->Bob
        $this->instance->incrementPairInRegistry(
            $this->claire,
            $this->bob,
            $registry,
            3
        );
        $actualPair = $registry->get($this->claire, $this->bob);
        $this->assertEquals(3, $actualPair->getVotes());

        $untouchedPair = $registry->get($this->alice, $this->bob);
        $this->assertEquals(18, $untouchedPair->getVotes());
    }

    public function testInitializeRegistryWithEmptyAgenda() : void
    {
        $registry = $this->instance->initializeRegistry(new Agenda());

        // N(N-1)
        $this->assertEquals(0, $registry->getCount());
    }
    public function testInitializeRegistryWithOneMemberAgenda() : void
    {
        $registry = $this->instance->initializeRegistry(
            new Agenda(
                new Ballot(
                    new CandidateList($this->alice)
                )
            )
        );

        // N(N-1)
        $this->assertEquals(0, $registry->getCount());
    }
    public function testInitializeRegistryWithTwoMemberAgenda() : void
    {
        $registry = $this->instance->initializeRegistry(
            new Agenda(
                new Ballot(
                    new CandidateList($this->alice),
                    new CandidateList($this->bob)
                )
            )
        );

        // N(N-1)
        $this->assertEquals(2, $registry->getCount());
    }
    public function testInitializeRegistryWithTwoMemberTiedAgenda() : void
    {
        $registry = $this->instance->initializeRegistry(
            new Agenda(
                new Ballot(
                    new CandidateList($this->alice, $this->bob)
                )
            )
        );

        // N(N-1)
        $this->assertEquals(2, $registry->getCount());
    }
    public function testInitializeRegistryWithThreeMemberAgenda() : void
    {
        $registry = $this->instance->initializeRegistry(
            new Agenda(
                new Ballot(
                    new CandidateList($this->alice),
                    new CandidateList($this->bob),
                    new CandidateList($this->claire)
                )
            )
        );

        // N(N-1)
        $this->assertEquals(6, $registry->getCount());
    }
    public function testInitializeRegistryWithThreeMemberTiedAgenda() : void
    {
        $registry = $this->instance->initializeRegistry(
            new Agenda(
                new Ballot(
                    new CandidateList(
                        $this->alice,
                        $this->bob,
                        $this->claire
                    )
                )
            )
        );

        // N(N-1)
        $this->assertEquals(6, $registry->getCount());
    }
    public function testCalculateWithEmptyBallot() : void
    {
        $ballot = new NBallot(0);
        $registry = $this->instance->calculate(
            new Agenda($ballot),
            $ballot
        );
        $this->assertEquals(0, $registry->getCount());
    }
    public function testCalculateForSimplePair() : void
    {
        $nBallots = [
            new NBallot(
                1,
                new CandidateList(
                    $this->alice
                ),
                new CandidateList(
                    $this->bob
                )
            )
        ];
        $registry = $this->instance->calculate(
            new Agenda(...$nBallots),
            ...$nBallots
        );
        // N(N-1)
        $this->assertEquals(2, $registry->getCount());
        $this->assertEquals(1, $registry->get($this->alice, $this->bob)->getVotes());
        $this->assertEquals(-1, $registry->get($this->bob, $this->alice)->getVotes());
    }
    public function testCalculateForSimpleTiedPair() : void
    {
        $nBallots = [
            new NBallot(
                1,
                new CandidateList(
                    $this->alice,
                    $this->bob
                )
            )
        ];
        $registry = $this->instance->calculate(
            new Agenda(...$nBallots),
            ...$nBallots
        );
        // N(N-1)
        $this->assertEquals(2, $registry->getCount());
        $this->assertEquals(0, $registry->get($this->alice, $this->bob)->getVotes());
        $this->assertEquals(0, $registry->get($this->bob, $this->alice)->getVotes());
    }
    public function testCalculateForTiedPairOfCandidatesAheadOfNonTiedCandidate() : void
    {
        $ballotCount = 42;
        $nBallots = [
            new NBallot(
                $ballotCount,
                new CandidateList(
                    $this->alice,
                    $this->bob
                ),
                new CandidateList(
                    $this->claire
                )
            )
        ];
        $registry = $this->instance->calculate(
            new Agenda(...$nBallots),
            ...$nBallots
        );
        // N(N-1)
        $this->assertEquals(6, $registry->getCount());
        //check that tied candidates' pairs reflect that they are tied
        $this->assertEquals(0, $registry->get($this->alice, $this->bob)->getVotes());
        $this->assertEquals(0, $registry->get($this->bob, $this->alice)->getVotes());
        //check that the pairs indicate that Claire is ranked behind Alice and Bob
        $this->assertEquals($ballotCount, $registry->get($this->alice, $this->claire)->getVotes());
        $this->assertEquals($ballotCount, $registry->get($this->bob, $this->claire)->getVotes());
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->claire, $this->alice)->getVotes());
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->claire, $this->bob)->getVotes());
    }
    public function testCalculateForTiedPairOfCandidatesBehindNonTiedCandidate() : void
    {
        $ballotCount = 31;
        $nBallots = [
            new NBallot(
                $ballotCount,
                new CandidateList(
                    $this->claire
                ),
                new CandidateList(
                    $this->alice,
                    $this->bob
                )
            )
        ];
        $registry = $this->instance->calculate(
            new Agenda(...$nBallots),
            ...$nBallots
        );
        // N(N-1)
        $this->assertEquals(6, $registry->getCount());
        //check that tied candidates' pairs reflect that they are tied
        $this->assertEquals(0, $registry->get($this->alice, $this->bob)->getVotes());
        $this->assertEquals(0, $registry->get($this->bob, $this->alice)->getVotes());
        //check that the pairs indicate that Claire is ahead of Alice and Bob
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->alice, $this->claire)->getVotes());
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->bob, $this->claire)->getVotes());
        $this->assertEquals($ballotCount, $registry->get($this->claire, $this->alice)->getVotes());
        $this->assertEquals($ballotCount, $registry->get($this->claire, $this->bob)->getVotes());
    }
    public function testCalculateForThreeNonTiedCandidates() : void
    {
        $ballotCount = 7;
        $nBallots = [
            new NBallot(
                $ballotCount,
                new CandidateList(
                    $this->claire
                ),
                new CandidateList(
                    $this->alice
                ),
                new CandidateList(
                    $this->bob
                )
            )
        ];
        $registry = $this->instance->calculate(
            new Agenda(...$nBallots),
            ...$nBallots
        );
        // N(N-1)
        $this->assertEquals(6, $registry->getCount());

        //Now check all N(N-1) pairs in the registry

        //check that Claire is ranked higher than Alice
        $this->assertEquals($ballotCount, $registry->get($this->claire, $this->alice)->getVotes());
        //check that Alice is ranked lower than Claire
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->alice, $this->claire)->getVotes());
        //check that Claire is ranked higher than Bob
        $this->assertEquals($ballotCount, $registry->get($this->claire, $this->bob)->getVotes());
        //check that Bob is ranked lower than Claire
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->bob, $this->claire)->getVotes());
        //check that Alice is ranked higher than Bob
        $this->assertEquals($ballotCount, $registry->get($this->claire, $this->alice)->getVotes());
        //check that Bob is ranked lower than Alice
        $this->assertEquals(-1 * $ballotCount, $registry->get($this->bob, $this->alice)->getVotes());
    }
}
