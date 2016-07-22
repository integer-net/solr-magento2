<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Data;

/**
 * Array-like data structure for collection pipelines
 *
 * @todo use dusank/knapsack for collection pipelines if used more often
 */
class ArrayCollection extends \ArrayIterator
{
    public static function fromArray(array $array)
    {
        return new static($array);
    }
    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static(\array_map($callback, $this->getArrayCopy()));
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        return new static(\array_filter($this->getArrayCopy(), $callback));
    }

    public function keys()
    {
        return new static(\array_keys($this->getArrayCopy()));
    }

    /**
     * @param callable $callback
     * @param null $initial
     * @return static
     */
    public function reduce(callable $callback, $initial = null)
    {
        return new static(\array_reduce($this->getArrayCopy(), $callback, $initial));
    }

    /**
     * @return static
     */
    public function unique()
    {
        return new static(\array_unique($this->getArrayCopy()));
    }

    /**
     * @return static
     */
    public function collapse()
    {
        return $this->reduce(function($carry, $item) {
            if (\is_array($item)) {
                return \array_merge($carry, $item);
            } else {
                return \array_merge($carry, [$item]);
            }
        }, []);
    }

    /**
     * @param array $values
     * @return static
     */
    public function without(array $values)
    {
        return new static(\array_filter($this->getArrayCopy(), function($value) use ($values) {
            return ! \in_array($value, $values);
        }));
    }

    /**
     * @return static
     */
    public function values()
    {
        return new static(\array_values($this->getArrayCopy()));
    }

}