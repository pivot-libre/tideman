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
        $bffBallotStrings = $this->splitOnNewLine($bffBallots);
        $ballots = $this->parseMultipleBallotStrings(...$bffBallotStrings);
        $result = $this->runHelper(...$ballots);
        return $result;
    }

    /**
     * @param string ...$bffBallots one BFF ballot per string
     * @return string BFF-encoded result
     */
    public function runAll(string ...$bffBallots) : string {
        $ballots = $this->parseMultipleBallotStrings($bffBallots);
        $result = $this->runHelper(...$ballots);
        return $result;
    }

    /**
     * @param NBallot ...$ballots
     * @return string BFF-encoded result
     */
    protected function runHelper(NBallot ... $ballots) : string
    {
        $calculator = new RankedPairsCalculator($this->tieBreaker);
        $results = $calculator->calculate(count($ballots), ...$ballots);
        $ranking = $results->getRanking();
        $bffRanking = $this->candidateRankingSerializer->serialize($ranking);
        return $bffRanking;
    }

    /**
     * @param string ...$strings to be trimmed and filtered
     * @return array of strings of nonzero length
     */
    protected function trimAndRemoveBlankStrings(string ...$strings) : array
    {
        //trim each line
        $strings = array_map("trim", $strings);
        //filter out blank lines
        $strings = array_filter($strings, function($line) {
            return '' !== $line;
        });
        return $strings;
    }

    /**
     * @param $string
     * @return array of strings, split on common newline characters
     */
    protected function splitOnNewLine($string) : array
    {
        //input could come from many different OSs, so must support splitting on all common newline representations
        $splitString = preg_split("/\r\n|\n|\r/", $string);
        return $splitString;
    }

    /**
     * @param string newline-delimited ballots formatted according to BFF
     * @return array of NBallots
     */
    protected function parseMultipleBallotStrings(... $bffStrings) : array
    {
        $filteredBffs = $this->trimAndRemoveBlankStrings(...$bffStrings);
        //parse each line
        $ballots = array_map(function (string $bffBallot) {
            return $this->parseSingleBallotString($bffBallot);
        }, $filteredBffs);
        return $ballots;
    }

    /**
     * @param string $singleBffString
     * @return NBallot
     */
    protected function parseSingleBallotString(string $singleBffString) : NBallot
    {
        $singleBffString = trim($singleBffString);
        $ballot = $this->nBallotParser->parse($singleBffString);
        return $ballot;
    }
}
