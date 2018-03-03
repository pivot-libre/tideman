<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class NBallotTest extends BallotTest
{
    public function testCount() : void
    {
        $expectedCount = 42;
        $this->setUpValues();
        $this->instance = new NBallot($expectedCount, ...$this->values);
        $this->concreteType = NBallot::class;
        $this->assertEquals($expectedCount, $this->instance->getCount());
    }
    public function testTieBreakingOnBallotWithoutTies() : void
    {
        $expectedCandidateOrder = NBallot::wrapEachInCandidateList($this->darius, $this->bob, $this->alice);
        $ballotWithoutTies = new NBallot(
            1,
            ...NBallot::wrapEachInCandidateList($this->darius, $this->bob, $this->alice)
        );

        $actualCandidateOrder = $ballotWithoutTies->getCopyWithRandomlyResolvedTies()->toArray();
        $this->assertEquals($expectedCandidateOrder, $actualCandidateOrder);
    }

    public function testTieBreakingOnBallotWithTies() : void
    {
        $expectedCandidateOrder = NBallot::wrapEachInCandidateList($this->bob, $this->alice, $this->darius);
        //seed the random number generator so that we can reliably test
        mt_srand(4242);
        try {
            $actualCandidateOrder = $this->instance->getCopyWithRandomlyResolvedTies()->toArray();
            $this->assertEquals($expectedCandidateOrder, $actualCandidateOrder);
        } finally {
            //reset the random number generator
            mt_srand();
        }
    }
}
