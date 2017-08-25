<?php
namespace PivotLibre\Tideman;

use \Fhaculty\Graph\Graph;
use \Fhaculty\Graph\Vertex;
use \Fhaculty\Graph\Set\Vertices;
// use \Fhaculty\Graph\Edge;
use \Graphp\Algorithms\Search\DepthFirst;
use Graphp\Algorithms\ShortestPath\BreadthFirst;

class RankedPairsGraph
{
    private $graph;
    //vertices have named attributes that we can associate artbitrary data to. This constant defines the name of an
    //attribute that we will use to store the original Candidate object that a vertex corresponds to.
    private const CANDIDATE_ATTRIBUTE_NAME = "candidate";

    public function __construct()
    {
        $this->graph = new Graph();
    }

    /**
    * @return CandidateList - a list of Candidates in descending order of preference. Candidates that are more
    * preferred have a lower index than Candidates that are less preferred.
    */
    public function getWinningCandidates() : CandidateList
    {
        $winningVertices = $this->getSourceVertices($this->graph);
        $tiedWinners = $this->getCandidatesFromVertices($winningVertices);
        return $tiedWinners;
    }
    /**
     * Locks in Margins in order of descending difference, ignoring any Margins that would contradict a
     * previously-locked-in Margin.
     * @param MarginList a MarginList whose Margins are sorted in order of descending difference and all differences are
     * greater than or equal to zero.
     */
    public function addMargins(MarginList $sortedMarginList) : CandidateList
    {
        foreach ($sortedMarginList as $margin) {
            $this->addMargin($margin);
        }
    }
    public function addMargin(Margin $margin)
    {
        $winnerVertex = $this->addCandidateToGraph($margin->getWinner(), $this->graph);
        $loserVertex = $this->addCandidateToGraph($margin->getLoser(), $this->graph);
        $newEdge = $winnerVertex->createEdgeTo($loserVertex);
        $newEdge->setWeight($margin->getDifference());
        //check for contradiction of stronger preferences
        if ($this->vertexIsInACycle($loserVertex)) {
            //don't contradict stronger preferences that have been locked in earlier
            $newEdge->destroy();
        }
    }

    /**
    * @todo #8 optimize this so that it uses a depth first search that returns early if the source node is discovered.
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
    public function getSourceVertices(Graph $graph) : Vertices
    {
        $sourceVertices = $graph->getVertices()->getVerticesMatch(function (Vertex $vertex) {
            return $vertex->getEdgesIn()->isEmpty();
        });
        return $sourceVertices;
    }

    /**
     * get Candidates from Vertices
     */
    public function getCandidatesFromVertices(Vertices $vertices) : CandidateList
    {
        $candidates = array_map(function (Vertex $vertex) {
            return $vertex->getAttribute(self::CANDIDATE_ATTRIBUTE_NAME);
        }, $vertices->getVector());
        $candidateList = new CandidateList(...$candidates);
    }
    /**
     * Adds a Vertex to the Graph that corresponds to the Candidate if it is not already present. Otherwise returns the
     * existing Vertex associated with the Candidate.
     * @param Candidate to add to the Graph
     * @param Graph to wihich we will add a vertex containing the Candidate.
     * @return a Vertex whose attribute 'self::CANDIDATE_ATTRIBUTE_NAME' is the parameterized Candidate.
     */
    public function addCandidateToGraph(Candidate $candidate, Graph $graph) : Vertex
    {
        $id = $candidate->getId();
        if ($graph->hasVertex($id)) {
            $vertex = $graph->getVertex($id);
        } else {
            $vertex = $graph->createVertex($id);
            $vertex->setAttribute(self::CANDIDATE_ATTRIBUTE_NAME, $candidate);
        }
        return $vertex;
    }
}
