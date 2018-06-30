<?php
namespace PivotLibre\Tideman;

use \DomainException;
use \Fhaculty\Graph\Graph;
use \Fhaculty\Graph\Vertex;
use \Fhaculty\Graph\Set\Vertices;
use \Graphp\Algorithms\Search\DepthFirst;
use Graphp\Algorithms\ShortestPath\BreadthFirst;
use PivotLibre\Tideman\Candidate;

class RankedPairsGraph
{
    private $graph;
    //vertices have named attributes that we can associate arbitrary data to. This constant defines the name of an
    //attribute that we will use to store the original Candidate object that a vertex corresponds to.
    public const CANDIDATE_ATTRIBUTE_NAME = "candidate";

    public function __construct()
    {
        $this->graph = new Graph();
    }

    /**
     * @return a human-readable adjacency list describing the graph
     */
    public function toString() : string
    {
        $graphStr = '';
        foreach ($this->graph->getVertices() as $vertex) {
            $graphStr .= "[" . $vertex->getId() . " : ";
            foreach ($vertex->getEdgesOut() as $edge) {
                $graphStr .= "( " . $edge->getWeight() . ", " . $edge->getVertexEnd()->getId() . ") ";
            }
            $graphStr .= " ] ";
        }
        return $graphStr;
    }
    /**
    * @return CandidateList - a list of Candidates who win this round of the election.
    */
    public function getWinningCandidates() : CandidateList
    {
        $winningVertices = $this->getSourceVertices();
        if ($winningVertices->isEmpty()) {
            $graphStr = $this->toString();
            throw new DomainException("Unable to find winners for the current graph. $graphStr");
        }
        $tiedWinners = $this->getCandidatesFromVertices($winningVertices);

        return $tiedWinners;
    }
    /**
     * Locks in Pairs in order of descending votes, ignoring any Pairs that would contradict a
     * previously-locked-in Pair.
     * @param PairList a PairList whose Pairs are sorted in order of descending votes and all votes are
     * greater than or equal to zero.
     */
    public function addPairs(PairList $sortedPairList) : void
    {
        foreach ($sortedPairList as $pair) {
            $this->addPair($pair);
        }
    }

    /**
     * Conditionally add an edge to the graph that originates at the node for `$pair->getWinner()` and ends at the node
     * for `$pair->getLoser()`. The edge is only added if it would not introduce a cycle.
     * @param Pair $pair
     */
    public function addPair(Pair $pair) : void
    {
        //add candidates to graph or get them if they already exist
        $winnerVertex = $this->addCandidateToGraph($pair->getWinner());
        $loserVertex = $this->addCandidateToGraph($pair->getLoser());

        $newEdge = $winnerVertex->createEdgeTo($loserVertex);
        $newEdge->setWeight($pair->getVotes());
        //check for contradiction of stronger preferences
        if ($this->vertexIsInACycle($loserVertex)) {
            //don't contradict stronger preferences that have been locked in earlier
            $newEdge->destroy();
        }
    }

    /**
     *
     * @todo #8 optimize this so that it uses a depth first search that returns early if the source node is discovered.
     * @param Vertex $vertex
     * @return bool
     */
    public function vertexIsInACycle(Vertex $vertex) : bool
    {
        //the graph algorithms package does not provide cycle detection. Luckily we can use a shortest path algorithm
        //to find if there is a path from a given vertex back to itself.
        $shortestPath = new BreadthFirst($vertex);
        $cycleExists = $shortestPath->hasVertex($vertex);
        return $cycleExists;
    }

    /**
     * Get nodes which have no inbound edges. These nodes are the winners.
     */
    public function getSourceVertices() : Vertices
    {
        $sourceVertices = $this->graph->getVertices()->getVerticesMatch(function (Vertex $vertex) {
            return $vertex->getEdgesIn()->isEmpty();
        });
        return $sourceVertices;
    }

    /**
     * @param Vertex $vertex
     * @return Candidate
     */
    public function getCandidateFromVertex(Vertex $vertex) : Candidate
    {
        $candidate = $vertex->getAttribute(self::CANDIDATE_ATTRIBUTE_NAME);
        return $candidate;
    }

    /**
     * get Candidates from Vertices
     */
    public function getCandidatesFromVertices(Vertices $vertices) : CandidateList
    {
        $candidates = array_map(function (Vertex $vertex) {
            return $this->getCandidateFromVertex($vertex);
        }, $vertices->getVector());
        $candidateList = new CandidateList(...$candidates);
        return $candidateList;
    }

    /**
     * Adds a node to the graph for every Candidate in the Agenda.
     * @param Agenda $agenda
     */
    public function addCandidatesFromAgenda(Agenda $agenda) : void
    {
        $this->addCandidatesFromList($agenda->getCandidates());
    }

    /**
     * Adds a node to the graph for every Candidate in the CandidateList.
     * @param CandidateList $candidateList
     */
    public function addCandidatesFromList(CandidateList $candidateList) : void
    {
        foreach ($candidateList as $candidate) {
            $this->addCandidateToGraph($candidate);
        }
    }

    /**
     * Adds a Vertex to the Graph that corresponds to the Candidate if it is not already present. Otherwise returns the
     * existing Vertex associated with the Candidate.
     * @param Candidate to add to the Graph
     * @return Vertex whose attribute 'self::CANDIDATE_ATTRIBUTE_NAME' is the parameterized Candidate.
     */
    public function addCandidateToGraph(Candidate $candidate) : Vertex
    {
        $id = $candidate->getId();
        if ($this->graph->hasVertex($id)) {
            $vertex = $this->graph->getVertex($id);
        } else {
            $vertex = $this->graph->createVertex($id);
            $vertex->setAttribute(self::CANDIDATE_ATTRIBUTE_NAME, $candidate);
        }
        return $vertex;
    }

    /**
     * @return Graph underlying Graph data structure
     */
    public function getGraph() : Graph
    {
        return $this->graph;
    }

    /**
     * @return bool true if there are more candidates, otherwise false
     */
    public function isEmpty() : bool
    {
        return $this->graph->getVertices()->isEmpty();
    }

    /**
     * Remove candidates from the graph so that they are no longer considered. The parameters to this method are
     * usually the same as the return value from @see RankedPairsGraph::getWinningCandidates() .
     * @param \PivotLibre\Tideman\Candidate ...$candidates
     */
    public function removeCandidates(Candidate ...$candidates) : void
    {
        foreach ($candidates as $candidate) {
            $vertex = $this->graph->getVertex($candidate->getId());
            $vertex->destroy();
        }
    }
}
