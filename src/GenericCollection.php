<?php
namespace PivotLibre\Tideman;

use IteratorAggregate;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

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
}
