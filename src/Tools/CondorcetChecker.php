<?php

namespace PivotLibre\Tideman\Tools;

use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\RankedPairsCalculator;
use PivotLibre\Tideman\NBallotParser;
use PivotLibre\Tideman\NBallot;

class CondorcetChecker
{
    public static function usage()
    {
        echo "Usage:\n";
        echo "  php condorcet_check.php -b <ballot-file> -c <cordorcet-file> [-s <seed>]\n";
        exit(1);
    }

    public static function parseRawBallots($ballot_path)
    {
        $handle = fopen($ballot_path, "r");
        if (! $handle) {
            echo 'Could not open file: '.$ballot_path;
            return null;
        }

        $ballots = array();
        $parser = new NBallotParser();

        while (($line = fgets($handle)) !== false) {
            array_push($ballots, $parser->parse($line));
        }

        fclose($handle);
        return $ballots;
    }

    public static function parseCondorcetRequirements($condorcet_path)
    {
        return json_decode(file_get_contents($condorcet_path), true);
    }

    public static function countUniqueCandidates($ballots)
    {
        $candidates = array();
        foreach ($ballots as $ballot) {
            foreach ($ballot as $candidate_list) {
                foreach ($candidate_list as $candidate) {
                    $candidates[$candidate->getId()] = true;
                }
            }
        }
        return count($candidates);
    }
    
    public static function checkTidemanAgainstCondorcet($ballots, $tie_breaker, $condorcet_path)
    {
        $calculator = new RankedPairsCalculator($tie_breaker);
        $num_of_winners = self::countUniqueCandidates($ballots);
        $winnerOrder = $calculator->calculate($num_of_winners, ...$ballots)->getRanking()->toArray();

        # display result
        echo "Winning Order:\n";
        for ($i = 0; $i < sizeof($winnerOrder); $i++) {
            echo "Candidate: '" . $winnerOrder[$i]->getId() . "'\n";
        }

        # parse condorcet requirements
        $condorcet = self::parseCondorcetRequirements($condorcet_path);

        # compare condorcet with tideman
        for ($i = 0; $i < sizeof($winnerOrder); $i++) {
            if ($i >= count($condorcet)) {
                break;
            }
            
            if ($condorcet[$i]["rank"] != $i+1) {
                echo("expected results not sorted by rank\n");
                exit(1);
            }
            
            $actual = $winnerOrder[$i]->getId();
            $expected = $condorcet[$i]["candidate"];

            if ($actual != $expected) {
                echo("candidate with ID=" . $actual . " unexpectedly beat candidate with ID=" . $expected . "\n");
                exit(1);
            }
        }
    }

    public static function main()
    {
        $options = getopt("b:c:s:");
        $ballot_path = $options["b"] ?? null;
        $condorcet_path = $options["c"] ?? null;
        $seed = $options["s"] ?? null;
        if (is_null($ballot_path) or is_null($condorcet_path)) {
            self::usage();
        }

        if (! is_null($seed)) {
            echo "Use seed: " . $seed . "\n";
            srand((int)$seed);
        }

        # parse ballots/candidates, and convert to Tideman library format
        $ballots = self::parseRawBallots($ballot_path);
        
        # check result for every possible tie breaker
        foreach ($ballots as $tie_breaker) {
            self::checkTidemanAgainstCondorcet($ballots, $tie_breaker, $condorcet_path);
        }

        return 0;
    }
}
