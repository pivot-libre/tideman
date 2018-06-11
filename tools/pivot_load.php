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
        echo "  php tools/pivot_load.php -b <ballot-file>\n";
        exit(1);
    }

    public static function ParseRawBallots($ballot_path) {
        $handle = fopen($ballot_path, "r");
        if (! $handle) {
            echo 'could not open file: '.$ballot_path;
            exit(1);
        }

        $ballots = array();

        while (($line = fgets($handle)) !== false) {
            $ballot = explode(">", trim($line));
            for ($i=0; $i<count($ballot); $i++) {
                $ballot[$i] = explode('=', $ballot[$i]);
            }
            array_push($ballots, $ballot);
        }

        fclose($handle);
        return $ballots;
    }

    public static function ExtractCandidates($ballots) {
        // name => tideman candidate
        $candidate_map = array();

        $candidate_id = 1;
        foreach ($ballots as $ballot) {
            foreach ($ballot as $candidate_list) {
                foreach ($candidate_list as $candidate) {
                    if (! array_key_exists($candidate, $candidate_map)) {
                        $candidate_map[$candidate] = new Candidate($candidate_id, $candidate);
                        $candidate_id++;
                    }
                }
            }
        }

        return $candidate_map;
    }

    public static function Main() {
        $options = getopt("b:");
        $ballot_path = $options["b"];
        if (is_null($ballot_path)) {
            self::Usage();
        }
        $ballots = self::ParseRawBallots($ballot_path);
        $candidate_map = self::ExtractCandidates($ballots);

        # convert to objects from the Tideman library
        for ($i=0; $i<count($ballots); $i++) {
            for ($j=0; $j<count($ballots[$i]); $j++) {
                for ($k=0; $k<count($ballots[$i][$j]); $k++) {
                    # convert: name => Tideman\Candidate
                    $ballots[$i][$j][$k] = $candidate_map[$ballots[$i][$j][$k]];
                }
                # convert: array => Tideman\CandidateList
                $ballots[$i][$j] = new CandidateList(...$ballots[$i][$j]);
            }
            # convert: array => Tideman\Ballot
            $ballots[$i] = new NBallot(1, ...$ballots[$i]);
        }

        # compute result
        $tie_breaker = $ballots[array_rand($ballots)];
        $calculator = new RankedPairsCalculator($tie_breaker);
        $num_of_winners = count($candidate_map);
        $winnerOrder = $calculator->calculate($num_of_winners, ...$ballots)->toArray();

        # display result
        echo "Winning Order:\n";
        for($i = 0; $i < sizeof($winnerOrder); $i++) {
            echo "Candidate: '" . $winnerOrder[$i]->getName() . "'\n";
        }
    }
}

Main::Main();
