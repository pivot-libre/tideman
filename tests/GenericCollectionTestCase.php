<?php
namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

abstract class GenericCollectionTestCase extends TestCase
{
    //array of values used to construct the GenericCollection as specified by the subclass
    protected $values;
    //GenericCollection instance as specified by the subclass
    protected $instance;
    //Concrete type of GenericCollection as specified by the subclass
    protected $concreteType;

    public function testValidConstructorCall() : void
    {
        $this->assertEquals(count($this->values), count($this->instance->toArray()));
        $this->assertEquals($this->values, $this->instance->toArray());
    }

    public function testCollectionReturnsCopyOfArray() : void
    {
        //if we modify the array returned by the generic collection
        $manipulatedArray = $this->instance->toArray();
        $manipulatedArray[] = 'bad value';
        $manipulatedArray[] = 'even worse value';
        //it should not affect the array stored within the collection
        $unManipulatedArray = $this->instance->toArray();
        $this->assertNotEquals(count($unManipulatedArray), count($manipulatedArray));
        $this->assertNotEquals($unManipulatedArray, $manipulatedArray);
    }

    public function testCollectionPreservesOriginalOrderAndValues() : void
    {
        $this->assertEquals($this->values, $this->instance->toArray());
    }

    public function testIteratorOrderMatchesOrderOfOriginalValues() : void
    {
        $valuesFromIterator = array();
        $iterator = $this->instance->getIterator();
        $counter = 0;
        while ($iterator->valid()) {
            $value = $iterator->current();
            $this->assertEquals($this->values[$counter], $value);
            $iterator->next();
            $counter++;
        }
        $this->assertEquals(count($this->values), $counter);
    }

    public function testConstructorTypeSafety() : void
    {
        $variousIllegalArguments = array(
            array(1),
            array(1,2),
            //should fail when passed an array of candidate lists.
            array($this->values)
        );
        foreach ($variousIllegalArguments as $illegalArg) {
            try {
                $instance = new $this->concreteType($illegalArg);
                //this should never run
                $this->assertEquals(true, false);
            } catch (\TypeError $e) {
                //pass
            }
            try {
                $instance = new $this->concreteType(...$illegalArg);
                //this should never run
                $this->assertEquals(true, false);
            } catch (\TypeError $e) {
                //pass
            }
        }

        //special case:
        try {
            //should fail when passed an array of Candidates WITHOUT using "..."
            $instance = new $this->concreteType($this->values);
            //this should never run
            $this->assertEquals(true, false);
        } catch (\TypeError $e) {
            //pass
        }

        $instance = new $this->concreteType(...$this->values);
        $this->assertNotNull($instance);
    }
}
