<?php
namespace PivotLibre\Tideman;

class MarginList extends GenericCollection
{
    public function __construct(Margin ...$margins)
    {
        $this->values = $margins;
    }
}
