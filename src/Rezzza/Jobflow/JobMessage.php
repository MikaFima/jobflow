<?php

namespace Rezzza\Jobflow;

use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Scheduler\ExecutionContext;
use Rezzza\Jobflow\Scheduler\JobGraph;
use Rezzza\Jobflow\Io;

/**
 * State representation between each job loop execution.
 * We should be able to pick up where we left job execution thanks to this object.
 */
class JobMessage
{
    protected $context;

    protected $payload;

    protected $id;

    public function __construct(JobContext $context, JobPayload $payload)
    {
        $this->context = $context;
        $this->payload = $payload;
        $this->generateId();
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }

    public function execute(ExecutionContext $execution, JobMessageFactory $factory)
    {
        return $execution->execute($this->context, $this->payload, $factory);
    }

    public function recoverJob(JobFactory $jobFactory)
    {
        return $jobFactory->create($this->context->jobId, $this->context->jobOptions);
    }

    public function getUniqName()
    {
        return $this->id;
    }

    public function generateId()
    {
        $this->id = $this->context->getMessageName().uniqid();
    }

    public function logState(LoggerInterface $logger)
    {
        $this->context->logState($logger);
    }

    /**
     * @param JobGraph $graph
     */
    public function createNextMsg($graph, $msgFactory, $forceRequeue = false)
    {
        $next = $graph->getNextJob();

        if (false === $forceRequeue && $next) {
            $this->context->moveTo($next);

            return $msgFactory->createMsg($this->context, $this->payload);
        }

        $this->context->tick();
        $this->context->reset();

        if (!$this->context->shouldRequeue()) {
            throw new NoMoreMessageException();
        }

        return $msgFactory->createMsg($this->context, new JobPayload);
    }

    /**
     * @param Job $job
     * @param JobGraph $graph
     */
    public function createPipeMsgs($job, $graph, $ctxFactory, $msgFactory)
    {
        $stdout = null;
        $msgs = [];

        if ($this->context->io) {
            $stdout = $this->context->io->getStdout();
        }

        foreach ($this->payload as $data) {
            if ($data->getValue() instanceof Io\Input) {
                $io = new Io\IoDescriptor($data->getValue(), $stdout);

                $context = $ctxFactory->create(
                    $job,
                    $io,
                    $graph->getNextJob(),
                    $data->getMetadata()
                );

                $msgs[] = $msgFactory->createMsg($context, new JobPayload);
            }
        }

        return $msgs;
    }

    /**
     * @param JobGraph $graph
     */
    public function shouldContinue($graph)
    {
        return !$this->isTerminated() && ($graph->hasNextJob() || $this->context->shouldRequeue());
    }

    public function getIo()
    {
        return $this->context->io;
    }

    public function isTerminated()
    {
        return $this->context->isTerminated();
    }

    /**
     * @param Job $job
     */
    public function currentChild($job)
    {
        return $this->context->currentChild($job);
    }

    public function initGraph(JobGraph $graph)
    {
        $this->context->initGraph($graph);
    }
}
