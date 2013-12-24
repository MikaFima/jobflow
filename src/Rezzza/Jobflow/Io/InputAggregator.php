<?php

namespace Rezzza\Jobflow\Io;

/**
 * InputAggregator
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class InputAggregator extends Input implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $inputs = array();

    /**
     * @param array $inputs inputs
     */
    public function __construct(array $inputs = [])
    {
        foreach ($inputs as $input) {
            $this->add($input);
        }
    }

    /**
     * @param Input $input input
     */
    public function add(Input $input)
    {
        $this->inputs[] = $input;
    }

    public function getDsn()
    {
        if ($current = $this->getIterator()->current()) {
            return $current->getDsn();
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->inputs);
    }

    public function count()
    {
        return count($this->inputs);
    }
}