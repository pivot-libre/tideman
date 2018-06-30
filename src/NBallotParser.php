<?php


namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class NBallotParser
{
    private $parser;

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
        $candidateRanking = $this->parser->parse($text);
        $ballot = new NBallot(1, ...$candidateRanking);
        return $ballot;
    }
}
