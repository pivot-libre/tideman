<?php

namespace PivotLibre\Tideman;

use \DomainException;
use PHPUnit\Framework\TestCase;
use PivotLibre\Tideman\RankedPairsGraph;
use PivotLibre\Tideman\RankedPairsCalculator;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Set\Vertices;

class RankedPairsGraphTest extends TestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CLAIRE_ID = "C";
    private const CLAIRE_NAME = "Claire";
    private $alice;
    private $bob;
    private $claire;
    private $instance;

    protected function setUp() :void
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $this->claire = new Candidate(self::CLAIRE_ID, self::CLAIRE_NAME);
        $this->instance = new RankedPairsGraph();
    }

    public function testAddCandidateToGraph() : void
    {
        //assert empty Graph to start with
        $this->assertEquals(0, $this->instance->getGraph()->getVertices()->count());
        $vertex = $this->instance->addCandidateToGraph($this->alice);
        //assert added to underlying graph
        $this->assertEquals(1, $this->instance->getGraph()->getVertices()->count());
        //assert candidate was attached to Vertex
        $this->assertEquals($this->alice, $vertex->getAttribute(RankedPairsGraph::CANDIDATE_ATTRIBUTE_NAME));

        //try adding it again. The underlying Graph should remain unchanged
        $secondVertex = $this->instance->addCandidateToGraph($this->alice);
        //assert underlying graph is still the same
        $this->assertEquals(1, $this->instance->getGraph()->getVertices()->count());
        //assert candidate is still attached to Vertex
        $this->assertEquals($this->alice, $vertex->getAttribute(RankedPairsGraph::CANDIDATE_ATTRIBUTE_NAME));
        //the second addition of a candidate should return the exact same object as the one returned by the
        //first addition
        $this->assertSame($vertex, $secondVertex);
    }

    public function testGetCandidatesFromOneLengthVertices() : void
    {
        //add just one vertex
        $this->instance->addCandidateToGraph($this->alice);
        $vertices = $this->instance->getGraph()->getVertices();
        $expected = new CandidateList($this->alice);
        $actual = $this->instance->getCandidatesFromVertices($vertices);
        $this->assertEquals($expected, $actual);
    }

    public function testGetCandidatesFromTwoLengthVertices() : void
    {
        //add two vertices
        $this->instance->addCandidateToGraph($this->alice);
        $this->instance->addCandidateToGraph($this->bob);
        $vertices = $this->instance->getGraph()->getVertices();
        $expected = new CandidateList($this->alice, $this->bob);
        $actual = $this->instance->getCandidatesFromVertices($vertices);
        $this->assertEquals($expected, $actual);
    }

    public function testOneVertexGraphDoesNotContainCycle() : void
    {
        $a = $this->instance->getGraph()->createVertex('a');
        $this->assertFalse($this->instance->vertexIsInACycle($a));
    }

    public function testVertexIsInACycle() : void
    {
        //create two vertices
        $a = $this->instance->getGraph()->createVertex('a');
        $b = $this->instance->getGraph()->createVertex('b');
        //assert that neither are considered to be in a cycle
        $this->assertFalse($this->instance->vertexIsInACycle($a));
        $this->assertFalse($this->instance->vertexIsInACycle($b));

        //create an edge between the two vertices
        $a->createEdgeTo($b);
        //assert that neither are considered to be in a cycle
        $this->assertFalse($this->instance->vertexIsInACycle($a));
        $this->assertFalse($this->instance->vertexIsInACycle($b));

        //create an edge between the two vertices that points in the opposite direction
        $b->createEdgeTo($a);
        //assert that both vertices are considered to be in a cycle
        $this->assertTrue($this->instance->vertexIsInACycle($a));
        $this->assertTrue($this->instance->vertexIsInACycle($b));

        //remove the edge that forms a cycle
        $edgeToA = $b->getEdgesTo($a)->getEdgeFirst();
        $edgeToA->destroy();
        //assert that neither vertex is considered to be part of a cycle
        $this->assertFalse($this->instance->vertexIsInACycle($a));
        $this->assertFalse($this->instance->vertexIsInACycle($b));
    }

    public function testLongerLengthCycles() : void
    {
        $ids = ['a', 'b', 'c', 'd', 'e'];

        //we should be able to say `$mostRecentVertex = null;` on the next line, but phpstan is incorrectly reporting a
        //"cannot call method on null" error`, so instead we trick phpstan by assigning null through an
        //immediately-invoked lambda function.
        $mostRecentVertex = (function () {
            return null;
        })();

        $currentVertex = null;

        //create vertices for each id in $ids. Create edges from the most recent Vertex to the the current Vertex
        foreach ($ids as $id) {
            $currentVertex = $this->instance->getGraph()->createVertex($id);
            if ($mostRecentVertex != null) {
                $mostRecentVertex->createEdgeTo($currentVertex);
            }
            $mostRecentVertex = $currentVertex;
        }

        //assert none of the vertices are in a cycle
        foreach ($this->instance->getGraph()->getVertices() as $vertex) {
            $this->assertFalse($this->instance->vertexIsInACycle($vertex));
        }

        //introduce a cycle
        $vertexA = $this->instance->getGraph()->getVertex('a');
        $vertexE = $this->instance->getGraph()->getVertex('e');

        $vertexE->createEdgeTo($vertexA);

        //assert that all of the vertices are in a cycle
        foreach ($this->instance->getGraph()->getVertices() as $vertex) {
            $this->assertTrue($this->instance->vertexIsInACycle($vertex));
        }

        //introduce a vertex and edge that are connected, but not part of the cycle
        $vertexZ = $this->instance->getGraph()->createVertex('z');
        $vertexC = $this->instance->getGraph()->getVertex('c');
        $vertexC->createEdgeTo($vertexZ);

        //assert that the new vertex is not considered part of a cycle
        $this->assertFalse($this->instance->vertexIsInACycle($vertexZ));
        //assert that the old vertex pointing to the new vertex is still considered to be in a cycle
        $this->assertTrue($this->instance->vertexIsInACycle($vertexC));
    }

    public function testAddOneMargin() : void
    {
        $margin = new Margin($this->alice, $this->bob, 1);
        $this->instance->addMargin($margin);
        $graph = $this->instance->getGraph();
        $aliceVertex = $graph->getVertex(self::ALICE_ID);
        $bobVertex = $graph->getVertex(self::BOB_ID);

        $this->assertEquals($this->alice, $this->instance->getCandidateFromVertex($aliceVertex));
        $this->assertEquals($this->bob, $this->instance->getCandidateFromVertex($bobVertex));
        $this->assertTrue($aliceVertex->hasEdgeTo($bobVertex));
        $this->assertFalse($bobVertex->hasEdgeTo($aliceVertex));
    }

    public function testAddTwoMarginsWithCycle() : void
    {
        $margin = new Margin($this->alice, $this->bob, 1);
        $reverseMargin = new Margin($this->bob, $this->alice, -1);

        $this->instance->addMargin($margin);
        $this->instance->addMargin($reverseMargin);
        $graph = $this->instance->getGraph();
        $aliceVertex = $graph->getVertex(self::ALICE_ID);
        $bobVertex = $graph->getVertex(self::BOB_ID);

        $this->assertEquals($this->alice, $this->instance->getCandidateFromVertex($aliceVertex));
        $this->assertEquals($this->bob, $this->instance->getCandidateFromVertex($bobVertex));
        $this->assertTrue($aliceVertex->hasEdgeTo($bobVertex));
        $this->assertFalse($bobVertex->hasEdgeTo($aliceVertex));
    }

    public function testGetEmptyGraphWinningCandidate() : void
    {
        $this->expectException(DomainException::class);
        $candidateList = $this->instance->getWinningCandidates();
    }

    public function testGetTrivialWinningCandidate() : void
    {
        $this->instance->addCandidateToGraph($this->alice);
        $candidateList = $this->instance->getWinningCandidates();
        $this->assertEquals(1, sizeof($candidateList->toArray()));
    }

    public function testGetSimpleWinningCandidate() : void
    {
        $margin = new Margin($this->alice, $this->bob, 1);
        $this->instance->addMargin($margin);
        $candidateList = $this->instance->getWinningCandidates();
        $candidates = $candidateList->toArray();
        $this->assertEquals(1, sizeof($candidates));
        $this->assertEquals($this->alice, $candidates[0]);
    }

    public function testSimpleToString() : void
    {
        $margin = new Margin($this->alice, $this->bob, 1);
        $reverseMargin = new Margin($this->bob, $this->alice, -1);
        $this->instance->addMargin($margin);
        $this->instance->addMargin($reverseMargin);
        $actual = $this->instance->toString();
        $expected = "[A : ( 1, B)  ] [B :  ] ";
        $this->assertEquals($expected, $actual);
    }
}
