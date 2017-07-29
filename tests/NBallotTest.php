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
}
