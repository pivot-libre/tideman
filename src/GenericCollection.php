<?php
namespace PivotLibre\Tideman;

use IteratorAggregate;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Since PHP lacks generics, and this library attempts to enforce a reasonable amount of type safety,
 * we need a way to ensure that members of a collection all have the same type. We achieve this by subclassing
 * GenericCollection for every type that we want a type-safe collection. The subclasses' constructor enforces type
 * safety using PHP's type hinting on variadic arguments. Subclasses inherit array-like behavior from this parent
 * abstract class.
 */
abstract class GenericCollection implements IteratorAggregate, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $values;

    public function toArray() : array
    {
        return $this->values;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }

    public function __toString() : string
    {
        $string = "[ ";
        $string .= join(", ", $this->values);
        $string .= " ]";
        return $string;
    }
}
