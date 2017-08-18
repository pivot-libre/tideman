<?php

namespace PivotLibre\Tideman;

class Grouper
{
    private $keyFunc;

    /**
     * @param callable $keyFunc a function that can convert any item in the specified $iterable to a scalar value.
     * $keyFunc should be written so that $keyFunc($a) === $keyFunc($b) for any items $a and $b that belong in the
     * same group.
     */
    public function __construct(callable $keyFunc)
    {
        $this->keyFunc = $keyFunc;
    }

    /**
     * @param iterable $iterable
     * @param callable $keyFunc a function that can convert any item in the specified $iterable to a scalar value.
     * $keyFunc should be written so that $keyFunc($a) === $keyFunc($b) for any items $a and $b that belong in the
     * same group.
     * @return an array that maps keys to arrays of associated items
     */
    protected function groupBy(iterable $iterable, callable $keyFunc) : array
    {
        $grouped = array();
        foreach ($iterable as $val) {
            $key = $keyFunc($val);
            $grouped[$key][] = $val;
        }
        return $grouped;
    }

    /**
     * @param iterable $iterable
     * @return an array that maps keys to arrays of associated items according to this instance's $keyFunc
     */
    public function group(iterable $iterable) : array
    {
        return $this->groupBy($iterable, $this->keyFunc);
    }
}
