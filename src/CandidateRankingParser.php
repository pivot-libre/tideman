<?php


namespace PivotLibre\Tideman;

class CandidateRankingParser
{
    private const ORDERED_DELIM = ">";
    private const EQUAL_DELIM = "=";

    /**
     * @param string $text
     *
     * @return CandidateRanking
     */
    public function parse(string $text) : CandidateRanking
    {
        $listOfCandidateLists = [];
        $orderedTokens = $this->tokenize($text, self::ORDERED_DELIM);

        foreach ($orderedTokens as $orderedToken) {
            $equallyPreferredTokens = $this->tokenize($orderedToken, self::EQUAL_DELIM);
            $equallyPreferredCandidates = [];
            foreach ($equallyPreferredTokens as $equallyPreferredToken) {
                $id = $equallyPreferredToken;
                $name = "";
                $candidate = new Candidate($id, $name);
                $equallyPreferredCandidates[] = $candidate;
            }
            $candidateList = new CandidateList(...$equallyPreferredCandidates);
            $listOfCandidateLists[] = $candidateList;
        }
        $result = new CandidateRanking(...$listOfCandidateLists);
        return $result;
    }

    private function tokenize(string $toTokenize, string $delim) : array
    {
        $tokens = [];
        $token = strtok($toTokenize, $delim);

        while ($token !== false) {
            $tokens[] = trim($token);
            $token = strtok($delim);
        }
        return $tokens;
    }
}
