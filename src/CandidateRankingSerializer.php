<?php


namespace PivotLibre\Tideman;

use InvalidArgumentException;

class CandidateRankingSerializer
{
    private const ORDERED_DELIM = ">";
    private const EQUAL_DELIM = "=";
    private const PROHIBITED_CHARACTERS = [
      '<', // only one direction of comparison is supported
      '*', // asterisks are used elsewhere to separate a ballot from how many times the same ballot occurred
      self::EQUAL_DELIM,
      self::ORDERED_DELIM
    ];

    /**
     * @param CandidateRanking $candidateRanking
     * @return string in BFF
     * @throws InvalidArgumentException if the ranking could not be serialized.
     */
    public function serialize(CandidateRanking $candidateRanking) : string
    {
        $flattenedCandidateList = array_map(
            function ($candidateList) {
                return $this->serializeEqualCandidates($candidateList);
            },
            $candidateRanking->toArray()
        );
        $serializedCandidateRanking = implode(self::ORDERED_DELIM, $flattenedCandidateList);
        return $serializedCandidateRanking;
    }

    public function serializeEqualCandidates(CandidateList $candidateList) : string
    {
        $candidateIds = array_map(
            function ($candidate) {
                return $this->getCandidateId($candidate);
            },
            $candidateList->toArray()
        );
        $serializedEquals = implode(self::EQUAL_DELIM, $candidateIds);
        return $serializedEquals;
    }

    public function getCandidateId(Candidate $candidate) : string
    {
        $id = $candidate->getId();
        $this->throwIfBlank($id);
        $this->throwIfProhibitedCharactersPresent($id);
        return $id;
    }
    /**
     * @throws InvalidArgumentException if trimmed string is of zero length
     * @param string $str
     */
    private function throwIfBlank(string $str) : void
    {
        if ('' === trim($str)) {
            throw new InvalidArgumentException(
                "Error serializing ranking of Candidates -- found blank where candidate id was expected"
            );
        }
    }

    /**
     * @param string $text
     * @throws InvalidArgumentException if $text contains anything in CandidateRankingParser::PROHIBITED_CHARACTERS
     */
    private function throwIfProhibitedCharactersPresent(string $text) : void
    {
        foreach (CandidateRankingSerializer::PROHIBITED_CHARACTERS as $prohibitedCharacter) {
            if ($this->contains($prohibitedCharacter, $text)) {
                throw new InvalidArgumentException(
                    "Found illegal character '$prohibitedCharacter' in candidate id '$text'"
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
