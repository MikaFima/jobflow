<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

class ExtractorProxy extends ETLProcessor implements ExtractorInterface
{
    public function execute(ExecutionContext $execution)
    {
        if ($execution->getLogger()) {
            $this->getProcessor()->setLogger($execution->getLogger());
        }

        $offset = $execution->getContextOption('offset');
        $limit = $execution->getContextOption('limit');
        $max = $execution->getContextOption('max');
        $total = $execution->getContextOption('total');

        // Limit total to the max if lesser
        if (null === $total) {
            $total = $this->count();

            if (null !== $max && $max < $total) {
                $total = $max;
            }

            $execution->setContextOption('total', $total);
        }

        if ($execution->getJobOption('offset', 0) > $offset) {
            $offset = $execution->getJobOption('offset');
            $execution->setContextOption('offset', $offset);
        }

        // Read data
        try {
            $data = $this->slice($offset, $limit);
        } catch (\OutOfBoundsException $e) {
            // Message has no more data and should not be spread
            $execution->terminate();
            $data = [];

            if ($execution->getLogger()) {
                $execution->getLogger()->debug('No data');
            }
        }

        // No data
        if (count($data) <= 0) {
            $execution->terminate();

            if ($execution->getLogger()) {
                $execution->getLogger()->debug('No data');
            }
        }

        // Store data read
        foreach ($data as $k => $v) {
            $execution->write($v, $this->getMetadataAccessor());
        }
    }

    public function slice($offset, $limit)
    {
        if (method_exists($this->getProcessor(), 'slice')) {
            return $this->getProcessor()->slice($offset, $limit);
        }

        $this->seek($offset);
        $data = [];

        for ($i = 0; $i < $limit && $this->valid(); $i++) {
            $data[] = $this->extract($this->createContext());
        }

        return $data;
    }

    public function count()
    {
        return $this->getProcessor()->count();
    }

    public function extract(ContextInterface $context)
    {
        return $this->getProcessor()->extract($context);
    }

    public function rewind()
    {
        return $this->getProcessor()->rewind();
    }

    public function current()
    {
        return $this->getProcessor()->current();
    }

    public function key()
    {
        return $this->getProcessor()->key();
    }

    public function next()
    {
        return $this->getProcessor()->next();
    }

    public function valid()
    {
        return $this->getProcessor()->valid();
    }

    public function seek($position)
    {
        return $this->getProcessor()->seek($position);
    }
}