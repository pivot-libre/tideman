<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class BffElectionRunnerTest extends TestCase
{
    private $instance;

    public function setUp() : void
    {
        $this->instance = new BffElectionRunner();
    }

    public function testSimpleElection() : void
    {
        $this->instance->setTieBreaker('A>B>C');
        $actual = $this->instance->run("A>B>C\nA>C>B");
        $expected = 'A>B>C';
        $this->assertEquals($expected, $actual);
    }
}
