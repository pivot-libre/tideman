<?php

namespace PivotLibre\Tideman\Tools;

use PivotLibre\Tideman\Candidate;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\RankedPairsCalculator;
use PivotLibre\Tideman\NBallot;


class BulkBallotLoader 
{
    public static function Usage() {
        echo "Usage:\n";
        echo "  php tools/pivot_load.php -b <ballot-file> -c <cordorcet-file>\n";
    }

    public static function ParseRawBallots($ballot_path) {
        $handle = fopen($ballot_path, "r");
        if (! $handle) {
            echo 'Could not open file: '.$ballot_path;
            return null;
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

    public static function ParseCondorcetRequirements($condorcet_path) {
        return json_decode(file_get_contents($condorcet_path), $assoc=true);
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

    public static function CheckTidemanAgainstCondorcet($candidate_map, $ballots, $tie_breaker, $condorcet_path) {
        $calculator = new RankedPairsCalculator($tie_breaker);
        $num_of_winners = count($candidate_map);
        $winnerOrder = $calculator->calculate($num_of_winners, ...$ballots)->toArray();

        # display result
        echo "Winning Order:\n";
        for($i = 0; $i < sizeof($winnerOrder); $i++) {
            echo "Candidate: '" . $winnerOrder[$i]->getName() . "'\n";
        }

        # parse condorcet requirements
        $condorcet = self::ParseCondorcetRequirements($condorcet_path);
        $rank = 1;
        for ($i=0; $i<count($condorcet); $i++) {
            $candidate_group = $condorcet[$i];
            if ($candidate_group["rank"] != $rank++) {
                die("expected results not sorted by rank\n");
            }
            $candidates = array_flip($candidate_group["candidates"]);
            $condorcet[$i] = $candidates;
        }

        for($i = 0; $i < sizeof($winnerOrder); $i++) {
            $c = $winnerOrder[$i]->getName();
            if (! isset($condorcet[0][$c])) {
                die("found an unexpected winner " . $c . " beat " . key($condorcet[0]) . "\n");
            }
            unset($condorcet[0][$c]);
            if (count($condorcet[0]) == 0) {
                array_shift($condorcet); // remove empty sets from the front
            }
        }
    }

    public static function Main() {
        $options = getopt("b:c:");
        $ballot_path = $options["b"] ?? null;
        $condorcet_path = $options["c"] ?? null;
        if (is_null($ballot_path) or is_null($condorcet_path)) {
            self::Usage();
            return 1;
        }

        # parse ballots/candidates, and convert to Tideman library format
        $ballots = self::ParseRawBallots($ballot_path);
        if (is_null($ballot_path)) {
            return 1;
        }
        $candidate_map = self::ExtractCandidates($ballots);

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
        foreach ($ballots as $tie_breaker) {
            self::CheckTidemanAgainstCondorcet($candidate_map, $ballots,
                                               $tie_breaker, $condorcet_path);
        }

        return 0;
    }
}
