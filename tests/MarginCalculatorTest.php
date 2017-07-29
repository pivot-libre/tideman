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
        $registry = new MarginRegistry();
        $registry->register(new Margin($this->alice, $this->bob, 0));
        $this->instance->incrementMarginInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            42
        );
        $actualMargin = $registry->get($this->alice, $this->bob);
        $this->assertEquals(42, $actualMargin->getMargin());
    }
    public function testUpdatePairIgnoreAnotherPairInRegistry() : void
    {
        $registry = new MarginRegistry();
        $registry->register(new Margin($this->alice, $this->bob, 0));
        $registry->register(new Margin($this->claire, $this->bob, 0));

        $this->instance->incrementMarginInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            5
        );
        $actualMargin = $registry->get($this->alice, $this->bob);
        $this->assertEquals(5, $actualMargin->getMargin());

        $untouchedMargin = $registry->get($this->claire, $this->bob);
        $this->assertEquals(0, $untouchedMargin->getMargin());
    }

    public function testTwoUpdatesOfTwoPairsInRegistry() : void
    {
        $registry = new MarginRegistry();
        $registry->register(new Margin($this->alice, $this->bob, 0));
        $registry->register(new Margin($this->claire, $this->bob, 0));

        //add 1 to Alice->Bob, don't touch Claire->Bob
        $this->instance->incrementMarginInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            1
        );
        $actualMargin = $registry->get($this->alice, $this->bob);
        $this->assertEquals(1, $actualMargin->getMargin());

        $untouchedMargin = $registry->get($this->claire, $this->bob);
        $this->assertEquals(0, $untouchedMargin->getMargin());

        //add 17 to Alice->Bob, don't touch Claire->Bob
        $this->instance->incrementMarginInRegistry(
            $this->alice,
            $this->bob,
            $registry,
            17
        );
        $actualMargin = $registry->get($this->alice, $this->bob);
        $this->assertEquals(18, $actualMargin->getMargin());

        $untouchedMargin = $registry->get($this->claire, $this->bob);
        $this->assertEquals(0, $untouchedMargin->getMargin());

        //Add 3 to Claire->Bob, don't touch Alice->Bob
        $this->instance->incrementMarginInRegistry(
            $this->claire,
            $this->bob,
            $registry,
            3
        );
        $actualMargin = $registry->get($this->claire, $this->bob);
        $this->assertEquals(3, $actualMargin->getMargin());

        $untouchedMargin = $registry->get($this->alice, $this->bob);
        $this->assertEquals(18, $untouchedMargin->getMargin());
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
        $registry = $this->instance->calculate(
            new NBallot(0)
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
            ...$nBallots
        );
        $this->assertEquals(2, $registry->getCount());
        $this->assertEquals(1, $registry->get($this->alice, $this->bob)->getMargin());
        $this->assertEquals(-1, $registry->get($this->bob, $this->alice)->getMargin());
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
            ...$nBallots
        );
        $this->assertEquals(2, $registry->getCount());
        $this->assertEquals(0, $registry->get($this->alice, $this->bob)->getMargin());
        $this->assertEquals(0, $registry->get($this->bob, $this->alice)->getMargin());
    }
}
