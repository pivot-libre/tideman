<?php


namespace PivotLibre\Tideman;


class BallotParser
{
    private static const ORDERED_DELIM = "<>";
    private static const EQUAL_DELIM = "=";

    public function parse(string $text) : Ballot
    {
        $listOfCandidateLists = [];
        $orderedTokens = $this->tokenize(ORDERED_DELIM);
        foreach($orderedTokens as $orderedToken) {
            $equallyPreferredTokens = $this->tokenize($orderedToken, EQUAL_DELIM);
            $equallyPreferredCandidates = [];
            foreach($equallyPreferredTokens as $equallyPreferredToken) {
                $candidate = new Candidate($equallyPreferredToken);
                $equallyPreferredCandidates[] = $candidate;
            }
            $candidateList = new CandidateList(...$equallyPreferredCandidates);
            $listOfCandidateLists[] = $candidateList;
        }
        $ballot = new Ballot(...$listOfCandidateLists);
        return $ballot;
    }

    private function tokenize(string $toTokenize, string $delim) : array
    {
        $tokens = [];
        $token = strtok($toTokenize, $delim);

        while ($token !== false) {
            $tokens[] = $token;
            $token = strtok($delim);
        }
        return $tokens;
    }
}