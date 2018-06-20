<?php


namespace PivotLibre\Tideman;

use \InvalidArgumentException;

class NBallotParser
{
    private const ORDERED_DELIM = ">";
    private const EQUAL_DELIM = "=";

    /**
     * @param string $text
     *
     * @return Ballot
     */
    public function parse(string $text) : NBallot
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
        $ballot = new NBallot(1, ...$listOfCandidateLists);
        return $ballot;
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
