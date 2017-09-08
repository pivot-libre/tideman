<?php

namespace PivotLibre\Tideman;

use PivotLibre\Tideman\TestScenario1;
use PivotLibre\Tideman\MarginCalculator;
use PHPUnit\Framework\TestCase;

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
}
