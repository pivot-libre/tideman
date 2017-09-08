<?php

namespace PivotLibre\Tideman;

use PivotLibre\Tideman\TestScenario1;
use PivotLibre\Tideman\MarginCalculator;
use PHPUnit\Framework\TestCase;

//https://docs.google.com/spreadsheets/d/1634wP6-N8GG2Fig-yjIOk7vPBn4AijXOrjq6Z2T1K8M/edit?usp=sharing
class MarginCalculatorIntegrationTest extends TestCase
{
    protected function checkMargins($expectedMargins, $ballots)
    {
        $agenda = new Agenda(...$ballots);
        $marginRegistry = (new MarginCalculator())->calculate($agenda, ...$ballots);
        $this->assertEquals(sizeof($expectedMargins), $marginRegistry->getCount());
        foreach ($expectedMargins as $expectedMargin) {
            $winner = $expectedMargin->getWinner();
            $loser = $expectedMargin->getLoser();
            $marginAsString = "{$winner->getId()} -> {$loser->getId()}";
            $actualMargin = $marginRegistry->get($winner, $loser);
            $this->assertEquals($expectedMargin, $actualMargin, $marginAsString);
        }
    }

    public function testScenario1() : void
    {
        //@TODO: ask Lucas why these differ from his expected results by a constant factor.
            $ballots = (new TestScenario1())->getBallots();
            $expectedMargins = [
                new Margin(new Candidate('MM'), new Candidate('DD'), 14),
                new Margin(new Candidate('MM'), new Candidate('SY'), 12),
                new Margin(new Candidate('MM'), new Candidate('YW'), 14),
                new Margin(new Candidate('MM'), new Candidate('RR'), 6),

                new Margin(new Candidate('DD'), new Candidate('MM'), -14),
                new Margin(new Candidate('DD'), new Candidate('SY'), 0),
                new Margin(new Candidate('DD'), new Candidate('YW'), 2),
                new Margin(new Candidate('DD'), new Candidate('RR'), 2),

                new Margin(new Candidate('SY'), new Candidate('MM'), -12),
                new Margin(new Candidate('SY'), new Candidate('DD'), 0),
                new Margin(new Candidate('SY'), new Candidate('YW'), 6),
                new Margin(new Candidate('SY'), new Candidate('RR'), 6),

                new Margin(new Candidate('YW'), new Candidate('MM'), -14),
                new Margin(new Candidate('YW'), new Candidate('DD'), -2),
                new Margin(new Candidate('YW'), new Candidate('SY'), -6),
                new Margin(new Candidate('YW'), new Candidate('RR'), 6),

                new Margin(new Candidate('RR'), new Candidate('MM'), -6),
                new Margin(new Candidate('RR'), new Candidate('DD'), -2),
                new Margin(new Candidate('RR'), new Candidate('SY'), -6),
                new Margin(new Candidate('RR'), new Candidate('YW'), -6)
            ];
            $this->checkMargins($expectedMargins, $ballots);
    }

    public function testScenario2() : void
    {
            $ballots = (new TestScenario2())->getBallots();
            $expectedMargins = [
                new Margin(new Candidate('MM'), new Candidate('BT'), 6),
                new Margin(new Candidate('MM'), new Candidate('CS'), 8),
                new Margin(new Candidate('MM'), new Candidate('FE'), -1),
                new Margin(new Candidate('MM'), new Candidate('RR'), 6),


                new Margin(new Candidate('BT'), new Candidate('MM'), -6),
                new Margin(new Candidate('BT'), new Candidate('CS'), 4),
                new Margin(new Candidate('BT'), new Candidate('FE'), 6),
                new Margin(new Candidate('BT'), new Candidate('RR'), 6),

                new Margin(new Candidate('CS'), new Candidate('MM'), -8),
                new Margin(new Candidate('CS'), new Candidate('BT'), -4),
                new Margin(new Candidate('CS'), new Candidate('FE'), 0),
                new Margin(new Candidate('CS'), new Candidate('RR'), 3),

                new Margin(new Candidate('FE'), new Candidate('MM'), 1),
                new Margin(new Candidate('FE'), new Candidate('BT'), -6),
                new Margin(new Candidate('FE'), new Candidate('CS'), 0),
                new Margin(new Candidate('FE'), new Candidate('RR'), 4),

                new Margin(new Candidate('RR'), new Candidate('MM'), -6),
                new Margin(new Candidate('RR'), new Candidate('BT'), -6),
                new Margin(new Candidate('RR'), new Candidate('CS'), -3),
                new Margin(new Candidate('RR'), new Candidate('FE'), -4)
            ];
            $this->checkMargins($expectedMargins, $ballots);
    }

    public function testScenario3() : void
    {
            $ballots = (new TestScenario3())->getBallots();
            $expectedMargins = [
                new Margin(new Candidate('CS'), new Candidate('MC'), -8),
                new Margin(new Candidate('CS'), new Candidate('BT'), -4),
                new Margin(new Candidate('CS'), new Candidate('FE'), 0),
                new Margin(new Candidate('CS'), new Candidate('RR'), 3),
                new Margin(new Candidate('CS'), new Candidate('MN'), -8),

                new Margin(new Candidate('MC'), new Candidate('CS'), 8),
                new Margin(new Candidate('MC'), new Candidate('BT'), 6),
                new Margin(new Candidate('MC'), new Candidate('FE'), 0),
                new Margin(new Candidate('MC'), new Candidate('RR'), 5),
                new Margin(new Candidate('MC'), new Candidate('MN'), -2),

                new Margin(new Candidate('BT'), new Candidate('CS'), 4),
                new Margin(new Candidate('BT'), new Candidate('MC'), -6),
                new Margin(new Candidate('BT'), new Candidate('FE'), 6),
                new Margin(new Candidate('BT'), new Candidate('RR'), 6),
                new Margin(new Candidate('BT'), new Candidate('MN'), -6),

                new Margin(new Candidate('FE'), new Candidate('CS'), 0),
                new Margin(new Candidate('FE'), new Candidate('MC'), 0),
                new Margin(new Candidate('FE'), new Candidate('BT'), -6),
                new Margin(new Candidate('FE'), new Candidate('RR'), 4),
                new Margin(new Candidate('FE'), new Candidate('MN'), 0),

                new Margin(new Candidate('RR'), new Candidate('CS'), -3),
                new Margin(new Candidate('RR'), new Candidate('MC'), -5),
                new Margin(new Candidate('RR'), new Candidate('BT'), -6),
                new Margin(new Candidate('RR'), new Candidate('FE'), -4),
                new Margin(new Candidate('RR'), new Candidate('MN'), -6),

                new Margin(new Candidate('MN'), new Candidate('CS'), 8),
                new Margin(new Candidate('MN'), new Candidate('MC'), 2),
                new Margin(new Candidate('MN'), new Candidate('BT'), 6),
                new Margin(new Candidate('MN'), new Candidate('FE'), 0),
                new Margin(new Candidate('MN'), new Candidate('RR'), 6)
            ];
            $this->checkMargins($expectedMargins, $ballots);
    }

    public function testScenario4() : void
    {
            $ballots = (new TestScenario4())->getBallots();
            $expectedMargins = [
                new Margin(new Candidate('CW'), new Candidate('BB'), 2),
                new Margin(new Candidate('CW'), new Candidate('CS'), 2),
                new Margin(new Candidate('CW'), new Candidate('BT'), 2),
                new Margin(new Candidate('CW'), new Candidate('SY'), 2),

                new Margin(new Candidate('BB'), new Candidate('CW'), -2),
                new Margin(new Candidate('BB'), new Candidate('CS'), 20),
                new Margin(new Candidate('BB'), new Candidate('BT'), 20),
                new Margin(new Candidate('BB'), new Candidate('SY'), 20),

                new Margin(new Candidate('CS'), new Candidate('CW'), -2),
                new Margin(new Candidate('CS'), new Candidate('BB'), -20),
                new Margin(new Candidate('CS'), new Candidate('BT'), 2),
                new Margin(new Candidate('CS'), new Candidate('SY'), 2),

                new Margin(new Candidate('BT'), new Candidate('CW'), -2),
                new Margin(new Candidate('BT'), new Candidate('BB'), -20),
                new Margin(new Candidate('BT'), new Candidate('CS'), -2),
                new Margin(new Candidate('BT'), new Candidate('SY'), 0),

                new Margin(new Candidate('SY'), new Candidate('CW'), -2),
                new Margin(new Candidate('SY'), new Candidate('BB'), -20),
                new Margin(new Candidate('SY'), new Candidate('CS'), -2),
                new Margin(new Candidate('SY'), new Candidate('BT'), 0)
            ];
            $this->checkMargins($expectedMargins, $ballots);
    }

    public function testTideman1987Example2() : void
    {
        $ballots = (new TestScenarioTideman1987Example2())->getBallots();
        $expectedMargins = [
            new Margin(new Candidate('V'), new Candidate('W'), 2),
            new Margin(new Candidate('V'), new Candidate('X'), 18),
            new Margin(new Candidate('V'), new Candidate('Y'), -14),
            new Margin(new Candidate('V'), new Candidate('Z'), -14),

            new Margin(new Candidate('W'), new Candidate('V'), -2),
            new Margin(new Candidate('W'), new Candidate('X'), 18),
            new Margin(new Candidate('W'), new Candidate('Y'), -14),
            new Margin(new Candidate('W'), new Candidate('Z'), -14),

            new Margin(new Candidate('X'), new Candidate('V'), -18),
            new Margin(new Candidate('X'), new Candidate('W'), -18),
            new Margin(new Candidate('X'), new Candidate('Y'), 16),
            new Margin(new Candidate('X'), new Candidate('Z'), 16),

            new Margin(new Candidate('Y'), new Candidate('V'), 14),
            new Margin(new Candidate('Y'), new Candidate('W'), 14),
            new Margin(new Candidate('Y'), new Candidate('X'), -16),
            new Margin(new Candidate('Y'), new Candidate('Z'), 2),

            new Margin(new Candidate('Z'), new Candidate('V'), 14),
            new Margin(new Candidate('Z'), new Candidate('W'), 14),
            new Margin(new Candidate('Z'), new Candidate('X'), -16),
            new Margin(new Candidate('Z'), new Candidate('Y'), -2)
        ];
        $this->checkMargins($expectedMargins, $ballots);
    }

    public function testTideman1987Example4() : void
    {
        $ballots = (new TestScenarioTideman1987Example4())->getBallots();
        $expectedMargins = [
            new Margin(new Candidate('W'), new Candidate('X'), 9),
            new Margin(new Candidate('W'), new Candidate('Y'), -5),
            new Margin(new Candidate('W'), new Candidate('Z'), 3),

            new Margin(new Candidate('X'), new Candidate('W'), -9),
            new Margin(new Candidate('X'), new Candidate('Y'), 13),
            new Margin(new Candidate('X'), new Candidate('Z'), 3),

            new Margin(new Candidate('Y'), new Candidate('W'), 5),
            new Margin(new Candidate('Y'), new Candidate('X'), -13),
            new Margin(new Candidate('Y'), new Candidate('Z'), 3),

            new Margin(new Candidate('Z'), new Candidate('W'), -3),
            new Margin(new Candidate('Z'), new Candidate('X'), -3),
            new Margin(new Candidate('Z'), new Candidate('Y'), -3),
        ];
        $this->checkMargins($expectedMargins, $ballots);
    }
}
