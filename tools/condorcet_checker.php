<?php

namespace PivotLibre\Tideman;

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once(dirname(__FILE__) .
             DIRECTORY_SEPARATOR . '..' .
             DIRECTORY_SEPARATOR . 'vendor/autoload.php');

class Main
{
    public static function Usage() {
        echo "Usage:\n";
        echo "  php tools/pivot_load.php -b <ballot-file> -c <cordorcet-file>\n";
        exit(1);
    }

    public static function ParseRawBallots($ballot_path) {
        $handle = fopen($ballot_path, "r");
        if (! $handle) {
            echo 'Could not open file: '.$ballot_path;
            return null;
        }

        $ballots = array();
        $parser = new BallotParser();

        while (($line = fgets($handle)) !== false) {
            array_push($ballots, $parser->parse($line));
        }

        fclose($handle);
        return $ballots;
    }

    public static function ParseCondorcetRequirements($condorcet_path) {
        return json_decode(file_get_contents($condorcet_path), true);
    }

    public static function CountUniqueCandidates($ballots) {
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
    
    public static function CheckTidemanAgainstCondorcet($ballots, $tie_breaker, $condorcet_path) {
        $calculator = new RankedPairsCalculator($tie_breaker);
        $num_of_winners = self::CountUniqueCandidates($ballots);
        $winnerOrder = $calculator->calculate($num_of_winners, ...$ballots)->toArray();

        # display result
        echo "Winning Order:\n";
        for($i = 0; $i < sizeof($winnerOrder); $i++) {
            echo "Candidate: '" . $winnerOrder[$i]->getId() . "'\n";
        }

        # parse condorcet requirements
        $condorcet = self::ParseCondorcetRequirements($condorcet_path);
        $rank = 1;
        for ($i=0; $i<count($condorcet); $i++) {
            $candidate_group = $condorcet[$i];
            if ($candidate_group["rank"] != $rank++) {
                echo("expected results not sorted by rank\n");
                exit(1);
            }
            $candidates = array_flip($candidate_group["candidates"]);
            $condorcet[$i] = $candidates;
        }

        for($i = 0; $i < sizeof($winnerOrder); $i++) {
            $c = $winnerOrder[$i]->getId();

            // does condorcet specify which candidates should win next?
            if (! isset($condorcet[0])) {
                break;
            }

            // is the winner in the list of expected winners?
            if (! isset($condorcet[0][$c])) {
                echo("candidate with ID=" . $c . " unexpectedly beat candidate with ID=" . key($condorcet[0]) . "\n");
                exit(1);
            }
            unset($condorcet[0][$c]);

            // no more candidates expected to tie in this group
            if (count($condorcet[0]) == 0) {
                array_shift($condorcet);
            }
        }
    }

    public static function Main() {
        $options = getopt("b:c:");
        $ballot_path = $options["b"] ?? null;
        $condorcet_path = $options["c"] ?? null;
        if (is_null($ballot_path) or is_null($condorcet_path)) {
            self::Usage();
        }

        # parse ballots/candidates, and convert to Tideman library format
        $ballots = self::ParseRawBallots($ballot_path);
        
        # check result for every possible tie breaker
        foreach ($ballots as $tie_breaker) {
            self::CheckTidemanAgainstCondorcet($ballots, $tie_breaker, $condorcet_path);
        }

        return 0;
    }
}

exit(Main::Main());