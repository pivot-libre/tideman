<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class WinningVoteRegistrarTest extends TestCase
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
    private $agenda;
    private $registry;

    protected function setUp()
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);

        $this->agenda = new Agenda();
        $this->agenda->addCandidates($this->alice, $this->bob, $this->claire);
        $this->instance = new WinningVoteRegistrar();
        $this->registry = $this->instance->initializeRegistry($this->agenda);
    }

    public function testIndifferenceNotIncrementedInTheAbsenceOfTies() : void
    {
        $this->instance->updateRegistry(
            $this->registry,
            1,
            $this->alice,
            $this->bob,
            1
        );

        $aOverB = $this->registry->get($this->alice, $this->bob);
        $this->assertEquals(0, $aOverB->getIndifference());
        $this->assertEquals(1, $aOverB->getVotes());

        $theOtherPairs = array_filter($this->registry->getAll()->toArray(), function ($pair) {
            return
                $pair->getWinner() != $this->alice && $pair->getLoser() != $this->bob;
        });

        foreach ($theOtherPairs as $pair) {
            $this->assertEquals(0, $pair->getIndifference());
            $this->assertEquals(0, $pair->getVotes());
        }
    }

    public function testIndifferenceAndVotesIncrementedWhenTiesArePresent() : void
    {
        $this->instance->updateRegistry(
            $this->registry,
            0,
            $this->alice,
            $this->bob,
            1
        );
        $aOverB = $this->registry->get($this->alice, $this->bob);
        $this->assertEquals(1, $aOverB->getIndifference());
        $this->assertEquals(1, $aOverB->getVotes());

        $bOverA = $this->registry->get($this->bob, $this->alice);
        $this->assertEquals(1, $bOverA->getIndifference());
        $this->assertEquals(1, $bOverA->getVotes());

        $theOtherPairs = array_filter($this->registry->getAll()->toArray(), function ($pair) {
            return
               ($pair->getWinner() != $this->alice && $pair->getLoser() != $this->bob)
               &&
               ($pair->getWinner() != $this->bob && $pair->getLoser() != $this->alice)
            ;
        });

        foreach ($theOtherPairs as $pair) {
            $this->assertEquals(0, $pair->getIndifference());
            $this->assertEquals(0, $pair->getVotes());
        }
    }
}
