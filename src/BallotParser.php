<?php


namespace PivotLibre\Tideman;
use \InvalidArgumentException;

class BallotParser
{
    private const ORDERED_DELIM = "<>";
    private const EQUAL_DELIM = "=";

    /**
     * @param string $text
     *
     * @return Ballot
     */
    public function parse(string $text) : Ballot
    {
        $this->enforceOneDirection($text);
        $listOfCandidateLists = [];
        $orderedTokens = $this->tokenize($text, self::ORDERED_DELIM);
        if ($this->contains("<", $text)) {
            $orderedTokens = array_reverse($orderedTokens);
        }
        foreach ($orderedTokens as $orderedToken) {
            $equallyPreferredTokens = $this->tokenize($orderedToken, self::EQUAL_DELIM);
            $equallyPreferredCandidates = [];
            foreach ($equallyPreferredTokens as $equallyPreferredToken) {
                $candidate = new Candidate($equallyPreferredToken);
                $equallyPreferredCandidates[] = $candidate;
            }
            $candidateList = new CandidateList(...$equallyPreferredCandidates);
            $listOfCandidateLists[] = $candidateList;
        }
        $ballot = new Ballot(...$listOfCandidateLists);
        return $ballot;
    }

    /**
     * @param $text
     * @throws \InvalidArgumentException if the ballot string contains both ">" and "<"
     */
    private function enforceOneDirection($text) : void
    {
        if (
            $this->contains(">", $text)
            &&
            $this->contains("<", $text)
        ) {
            throw new InvalidArgumentException("Ballot contained both '>' and '<'. It should only contain one or the other");
        }
    }


    /**
     * @param $needle
     * @param $haystack
     * @return bool true if $needle is in $haystack, false otherwise.
     */
    private function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
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