<?php


namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class NBallotParser
{
    private $parser;
    public const OPTIONAL_MULTIPLIER = "*";
    public function __construct()
    {
        $this->parser = new CandidateRankingParser();
    }

    /**
     * @param string $text
     *
     * @return NBallot
     * @throws InvalidArgumentException if the text could not be parsed.
     */
    public function parse(string $text) : NBallot
    {
        //default n to one
        $n = 1;
        $tokens = $this->tokenize($text);
        if (2 === sizeof($tokens)) {
            $nToken = $tokens[0];
            $rankingToken = $tokens[1];

            $n = $this->parseN($nToken);
            $candidateRanking = $this->parser->parse($rankingToken);
        } elseif (1 === sizeof($tokens)) {
            $candidateRanking = $this->parser->parse($tokens[0]);
        } else {
            $msg = "Could not parse NBallot. Got '$text'. Expected things like 'A>B=C', '1 * A>B>C', or '42*C=B>A'.";
            throw new InvalidArgumentException($msg);
        }
        $ballot = new NBallot($n, ...$candidateRanking);
        return $ballot;
    }

    /**
     * @param string $text
     * @return array of trimmed string tokens
     */
    private function tokenize(string $text) : array
    {
        $tokens = explode(NBallotParser::OPTIONAL_MULTIPLIER, $text);
        $tokens = array_map(function ($token) {
            return trim($token);
        }, $tokens);
        return $tokens;
    }

    /**
     * @param string $text
     * @return int the positive integer counting the number of times this Ballot occurs
     */
    private function parseN(string $text) : int
    {
        $n = (int)$text;

        if (floatval($text) != intval($text)) {
            throw new InvalidArgumentException("Only integer numbers of ballots are possible. Got '$text'.");
        }

        if (1 > $n) {
            $msg = "A ballot can only be represented a positive integer number of times. Got '$text' instead";
            throw new InvalidArgumentException($msg);
        }

        return $n;
    }
}
