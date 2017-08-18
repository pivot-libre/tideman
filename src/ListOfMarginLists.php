<?php
namespace PivotLibre\Tideman;

class ListOfMarginLists extends GenericCollection
{
    public function __construct(MarginList ...$marginLists)
    {
        $this->values = $marginLists;
    }
}
