<?php


namespace PivotLibre\Tideman;

use InvalidArgumentException;

class CandidateRankingParser
{
    private const ORDERED_DELIM = ">";
    private const EQUAL_DELIM = "=";
    private const PROHIBITED_CHARACTERS = [
      '<', // only one direction of comparison is supported
      '*', // asterisks are used elsewhere to separate a ballot from how many times the same ballot occurred
    ];

    /**
     * @param string $text
     *
     * @return CandidateRanking
     * @throws InvalidArgumentException if the text could not be parsed.
     */
    public function parse(string $text) : CandidateRanking
    {
        $this->throwIfProhibitedCharactersPresent($text);
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
        $ranking = new CandidateRanking(...$listOfCandidateLists);
        return $ranking;
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
            throw new InvalidArgumentException(
                "Error parsing ranking of Candidates -- found blank where candidate id was expected"
            );
        }
    }

    /**
     * @param string $text
     * @throws InvalidArgumentException if $text contains anything in CandidateRankingParser::PROHIBITED_CHARACTERS
     */
    private function throwIfProhibitedCharactersPresent(string $text) : void
    {
        foreach (CandidateRankingParser::PROHIBITED_CHARACTERS as $prohibitedCharacter) {
            if ($this->contains($prohibitedCharacter, $text)) {
                throw new InvalidArgumentException(
                    "Found illegal character '$prohibitedCharacter' in string '$text'"
                );
            }
        }
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @return bool - true if $needle in $haystack, false otherwise
     */
    private function contains(string $needle, string $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }
}
