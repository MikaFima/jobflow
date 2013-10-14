<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;

use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class ExtractorProxy extends ETLProcessor implements ExtractorInterface
{
    public function execute(JobInput $input, JobOutput &$output, ExecutionContext $execution)
    {
        if ($execution->getLogger()) {
            $this->getProcessor()->setLogger($execution->getLogger());
        }

        $offset = $execution->getGlobalOption('offset');
        $limit = $execution->getGlobalOption('limit');
        $max = $execution->getGlobalOption('max');
        $total = $execution->getGlobalOption('total');

        // Limit total to the max if lesser
        if (null === $total) {
            $total = $this->count();

            if (null !== $max && $max < $total) {
                $total = $max;
            }

            $execution->setGlobalOption('total', $total);
        }

        if ($execution->getJobOption('offset', 0) > $offset) {
            $offset = $execution->getJobOption('offset');
            $execution->setGlobalOption('offset', $offset);
        }

        // Move offset to the specified position
        try {
            $this->seek($offset);
        } catch (\OutOfBoundsException $e) {
            // Message has no more data and should not be spread
            $output->end();

            if ($execution->getLogger()) {
                $execution->getLogger()->debug('No data');
            }
        }

        // Store data read
        for ($i = 0; $i < $limit && $this->valid(); $i++) {
            if ($this->key() > $total) {
                break;
            }

            $output->write($this->current(), $offset + $i);
            $this->next();
        }

        return $output;
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