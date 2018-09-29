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
     */
    public function run(string $bffBallots) : string
    {
        //parse ballots
        $splitBffBallots = preg_split("/\r\n|\n|\r/", $bffBallots);
        $ballots = array_map(function (string $bffBallot) {
            return $this->ballotParser->parse($bffBallot);
        }, $splitBffBallots);
        
        //run election
        $calculator = new RankedPairsCalculator($this->tieBreaker);
        $results = $calculator->calculate(count($ballots), ...$ballots);

        //get ranking and convert to BFF string
        $ranking = $results->getRanking();
        $bffRanking = $this->candidateRankingSerializer->serialize($ranking);

        return $bffRanking;
    }
}
