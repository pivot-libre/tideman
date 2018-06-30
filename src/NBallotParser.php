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
     * @throws InvalidArgumentException if the text could not be parsed.
     */
    public function parse(string $text) : NBallot
    {
        $listOfCandidateLists = [];
        $orderedTokens = $this->tokenize($text, self::ORDERED_DELIM);

        foreach ($orderedTokens as $orderedToken) {
            $this->throwIfBlank($orderedToken);
            $equallyPreferredTokens = $this->tokenize($orderedToken, self::EQUAL_DELIM);
            $equallyPreferredCandidates = [];
            foreach ($equallyPreferredTokens as $equallyPreferredToken) {
                $this->throwIfBlank($equallyPreferredToken);
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

    /**
     * Tokenizes $toTokenize on $delim and trims each token
     * @param string $toTokenize
     * @param string $delim
     * @return array of trimmed tokens
     */
    private function tokenize(string $toTokenize, string $delim) : array
    {
        $tokens = explode($delim, $toTokenize);
        $trimmedTokens = array_map(function ($token) {
            return trim($token);
        }, $tokens);
        return $trimmedTokens;
    }

    /**
     * @throws InvalidArgumentException if trimmed string is of zero length
     * @param string $str
     */
    private function throwIfBlank(string $str) : void
    {
        if ('' === trim($str)) {
            throw new InvalidArgumentException("Error Parsing Ballot -- found blank where candidate id was expected");
        }
    }
}