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
    public function testGetWinnerAliceAndLoserBob() : void
    {
        $candidateIdToRankMap = [
            self::ALICE_ID => 0,
            self::BOB_ID => 1
        ];

        list($actualWinner, $actualLoser) = $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );

        $this->assertEquals($this->alice, $actualWinner);
        $this->assertEquals($this->bob, $actualLoser);
    }
    public function testGetWinnerBobAndLoserAlice() : void
    {
        $candidateIdToRankMap = [
            self::ALICE_ID => 1,
            self::BOB_ID => 0
        ];

        list($actualWinner, $actualLoser) = $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );

        $this->assertEquals($this->bob, $actualWinner);
        $this->assertEquals($this->alice, $actualLoser);
    }
    public function testGetWinnerAndLoserWithInnerCandidateMissingFromMap() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $candidateIdToRankMap = [
            self::ALICE_ID => 0,
        ];
        $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );
    }
    public function testGetWinnerAndLoserWithOuterCandidateMissingFromMap() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $candidateIdToRankMap = [
            self::BOB_ID => 0,
        ];
        $this->instance->getWinnerAndLoser(
            $this->alice,
            $this->bob,
            $candidateIdToRankMap
        );
    }
    public function testGetCandidateIdToRankMapFromEmptyBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot());
        $this->assertEmpty($map);
    }
    public function testGetCandidateIdToRankMapFromOneCandidateBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
    }
    public function testGetCandidateIdToRankMapFromTwoCandidateBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice),
            new CandidateList($this->bob)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
        $this->assertSame(1, $map[self::BOB_ID]);
    }
    public function testGetCandidateIdToRankMapFromTwoTiedCandidateBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice, $this->bob)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
        $this->assertSame(0, $map[self::BOB_ID]);
    }
    public function testGetCandidateIdToRankMapFromMixedTiedAndNotTiedBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice, $this->bob),
            new CandidateList($this->claire)
        ));
        $this->assertSame(0, $map[self::ALICE_ID]);
        $this->assertSame(0, $map[self::BOB_ID]);
        $this->assertSame(1, $map[self::CLAIRE_ID]);
    }
    public function testGetCandidateIdToRankMapFromMixedNotTiedAndTiedBallot() : void
    {
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->claire),
            new CandidateList($this->alice, $this->bob)
        ));
        $this->assertSame(0, $map[self::CLAIRE_ID]);
        $this->assertSame(1, $map[self::ALICE_ID]);
        $this->assertSame(1, $map[self::BOB_ID]);
    }
    public function testGetCandidateIdToRankMapFailsOnBallotWithDuplicates() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $map = $this->instance->getCandidateIdToRankMap(new Ballot(
            new CandidateList($this->alice, $this->alice)
        ));
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
    public function testCalculateEmptyAgendaAndAbsentBallot() : void
    {
        $registry = $this->instance->calculate(new Agenda());
        $this->assertEquals(0, $registry->getCount());
    }
    public function testCalculateEmptyAgendaAndEmptyBallot() : void
    {
        $registry = $this->instance->calculate(
            new Agenda(),
            new NBallot(0)
        );
        $this->assertEquals(0, $registry->getCount());
    }
    public function testCalculateEmptyAgendaAndPopulatedBallots() : void
    {
        $registry = $this->instance->calculate(
            new Agenda(),
            new NBallot(
                1,
                new CandidateList(
                    $this->alice,
                    $this->bob
                )
            )
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
        $agenda = new Agenda(...$nBallots);
        $registry = $this->instance->calculate(
            $agenda,
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
        $agenda = new Agenda(...$nBallots);
        $registry = $this->instance->calculate(
            $agenda,
            ...$nBallots
        );
        $this->assertEquals(2, $registry->getCount());
        $this->assertEquals(0, $registry->get($this->alice, $this->bob)->getMargin());
        $this->assertEquals(0, $registry->get($this->bob, $this->alice)->getMargin());
    }
}
