<?php


namespace PivotLibre\Tideman;

class BallotParser
{
    private $parser;
    public function __construct()
    {
        $this->parser = new CandidateRankingParser();
    }

    public function parse(string $text) : Ballot
    {
        $candidateRanking = $this->parser->parse($text);
        $ballot = new Ballot(...$candidateRanking);
        return $ballot;
    }
}
