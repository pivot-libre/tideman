<?php

namespace PivotLibre\Tideman;

class BallotSerializer
{
    private $serializer;
    public function __construct()
    {
        $this->serializer = new CandidateRankingSerializer();
    }

    /**
     * @param Ballot $ballot the ballot to serialize
     * @return string the ballot in BFF
     */
    public function serialize(Ballot $ballot) : string
    {
        $serializedBallot = $this->serializer->serialize($ballot);
        return $serializedBallot;
    }
}
