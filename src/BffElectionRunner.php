<?php
namespace PivotLibre\Tideman;

class BffElectionRunner
{
    private $ballotParser;
    private $candidateRankingSerializer;
    private $tieBreaker;

    public function __construct()
    {
        $this->ballotParser = new BallotParser();
        $this->nBallotParser = new NBallotParser();
        $this->candidateRankingSerializer = new CandidateRankingSerializer();
    }

    /**
     * @param string $bffTieBreaker a tie breaking ballot formatted according to BFF.
     */
    public function setTieBreaker(string $bffTieBreaker) : void
    {
        $tieBreaker = $this->ballotParser->parse($bffTieBreaker);
        $this->tieBreaker = $tieBreaker;
    }

    /**
     * @param string newline-delimited ballots formatted according to BFF
     * @return string BFF-encoded result
     */
    public function run(string $bffBallots) : string
    {
        $ballots = $this->parseMultiBallotString($bffBallots);
        $calculator = new RankedPairsCalculator($this->tieBreaker);
        $results = $calculator->calculate(count($ballots), ...$ballots);
        $ranking = $results->getRanking();
        $bffRanking = $this->candidateRankingSerializer->serialize($ranking);

        return $bffRanking;
    }

    /**
     * @param string newline-delimited ballots formatted according to BFF
     * @return array of NBallots
     */
    public function parseMultiBallotString($bffString) : array
    {
        //parse ballots

        //input could come from many different OSs, so must support splitting on all common newline representations
        $splitBffBallots = preg_split("/\r\n|\n|\r/", $bffString);
        //trim each line
        $splitBffBallots = array_map("trim", $splitBffBallots);
        //filter out blank lines
        $splitBffBallots = array_filter($splitBffBallots, function($line) {
            return '' !== $line;
        });
        //parse each line
        $ballots = array_map(function (string $bffBallot) {
            return $this->nBallotParser->parse($bffBallot);
        }, $splitBffBallots);
        return $ballots;
    }
}
