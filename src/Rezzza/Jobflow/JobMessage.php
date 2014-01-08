<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * State representation between each job loop execution.
 * We should be able to pick up where we left job execution thanks to this object.
 */
class JobMessage
{
    protected $context;

    protected $payload;

    public function __construct(JobContext $context, JobPayload $payload)
    {
        $this->context = $context;
        $this->payload = $payload;
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }

    public function createStartedJobExecution($jobFactory)
    {
        $execution = new ExecutionContext(
            $jobFactory->create($this->context->jobId, $this->context->jobOptions)
        );

        $execution->start($this);

        return $execution;
    }

    public function createEndedJobExecution($jobFactory)
    {
        $execution = new ExecutionContext(
            $jobFactory->create($this->context->jobId, $this->context->jobOptions)
        );

        $execution->end($this);

        return $execution;
    }

    public function initExecutionContext($executionContext)
    {
        $executionContext->initContext($this->context);
    }

    public function initExecutionInput($execution)
    {
        $execution->initInput($this->payload);
    }

    public function initExecutionOutput($execution)
    {
        $execution->initOutput($this->payload);
    }

    public function getUniqName()
    {
        return $this->context->getMessageName().uniqid();
    }

    public function logState($logger)
    {
        if (!$logger) {
            return;
        }

        $this->context->logState($logger);
    }
}