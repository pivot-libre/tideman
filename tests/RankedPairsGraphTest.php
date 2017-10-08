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
        $a = $this->instance->getGraph()->createVertex('a');
        $b = $this->instance->getGraph()->createVertex('b');
        $this->assertFalse($this->instance->vertexIsInACycle($a));
        $this->assertFalse($this->instance->vertexIsInACycle($b));

        $a->createEdgeTo($b);
        $this->assertFalse($this->instance->vertexIsInACycle($a));
        $this->assertFalse($this->instance->vertexIsInACycle($b));

        $b->createEdgeTo($a);
        $this->assertTrue($this->instance->vertexIsInACycle($a));
        $this->assertTrue($this->instance->vertexIsInACycle($b));
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
