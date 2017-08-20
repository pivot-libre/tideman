<?php
namespace PivotLibre\Tideman;

use PivotLibre\Tideman\ListOfMarginLists;

class MarginList extends GenericCollection
{
    private $grouper;

    public function __construct(Margin ...$margins)
    {
        $this->values = $margins;
        $this->grouper = new Grouper(function (Margin $margin) {
            return $margin->getDifference();
        });
    }
}
