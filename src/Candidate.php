<?php
namespace PivotLibre\Tideman;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Candidate implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $id;
    private $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name . "(" . $this->id . ")";
    }
}
