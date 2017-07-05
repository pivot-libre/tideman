<?php
namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

abstract class GenericCollectionTestCase extends TestCase
{
    protected function assertCollectionReturnsCopyOfArray(GenericCollection $collection, array $originalValues) : void
    {
        //if we modify the array returned by the generic collection
        $manipulatedArray = $collection->toArray();
        $manipulatedArray[] = 'bad value';
        $manipulatedArray[] = 'even worse value';
        //it should not affect the array stored within the collection
        $unManipulatedArray = $collection->toArray();
        $this->assertNotEquals(count($unManipulatedArray), count($manipulatedArray));
        $this->assertNotEquals($unManipulatedArray, $manipulatedArray);
    }

    protected function assertCollectionPreservesOriginalOrderAndValues(
        GenericCollection $collection,
        array $originalValues
    ) : void {
        $this->assertEquals($originalValues, $collection->toArray());
    }

    protected function assertIteratorOrderMatchesOrderOfOriginalValues(
        GenericCollection $collection,
        array $originalValues
    ) : void {
        $valuesFromIterator = array();
        $iterator = $collection->getIterator();
        $counter = 0;
        while ($iterator->valid()) {
            $value = $iterator->current();
            $this->assertEquals($originalValues[$counter], $value);
            $iterator->next();
            $counter++;
        }
        $this->assertEquals(count($originalValues), $counter);
    }
}
