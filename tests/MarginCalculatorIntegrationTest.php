<?php

namespace PivotLibre\Tideman;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use PHPUnit\Framework\TestCase;

class MarginCAlculatorIntegrationTest extends TestCase {
        private $serializer;

        public function __construct()
        {
            $normalizers = array(new ObjectNormalizer(), new ArrayDenormalizer());
            $encoders = array(new JsonEncoder());

            $this->serializer = new Serializer($normalizers, $encoders);
        }
        public function loadBallotsFromJSONFile($path) : array
        {
            $fileContents = file_get_contents($path);
            $ballots = $this->serializer->deserialize($fileContents, 'PivotLibre\Tideman\NBallot[]', 'json');
            return $ballots;
        }
        public function testScenario1() : void
        {
            $ballots = $this->loadBallotsFromJSONFile("tests/scenario1.json");
        }
}
