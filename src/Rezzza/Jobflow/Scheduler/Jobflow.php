<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerAwareTrait;

use Rezzza\Jobflow\JobContext;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * Handles job execution
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Jobflow
{
    use LoggerAwareTrait;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return JobInterface
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param JobInterface $job
     *
     * @return Jobflow
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
        $this->buildGraph();
        $this->job->setLocked(true);

        return $this;
    }

    /**
     * Adds message in transport layer
     *
     * @param JobMessage $msg
     *
     * @return Jobflow
     */
    public function addMessage(JobMessage $msg)
    {
        $this->transport->addMessage($msg);

        return $this;
    }

    /**
     * Creates init message to start execution.
     *
     * @return Jobflow
     */
    public function init()
    {
        if (null === $this->getJob()) {
            throw new \RuntimeException('You need to set a job');
        }

        $this->addMessage($this->getInitMessage());

        return $this;
    }

    /**
     * Handles a message in args or look to the transport for next one
     *
     * @param JobMessage|null $msg
     */
    public function run(JobMessage $msg = null)
    {
        if (null !== $msg) {
            return $this->runJob($msg);
        }

        while ($msg = $this->wait()) {
            if (!$msg instanceof JobMessage) {
                return;
            }

            $result = $this->runJob($msg);

            if ($result instanceof JobMessage) {
                $this->handleMessage($result);
            }
        }

        return $result;
    }

    /**
     * Waits for message
     *
     * @return JobMessage
     */
    public function wait()
    {
        return $this->transport->getMessage();
    }

    /**
     * Need to work on this method to make it more simple and readable
     */
    public function handleMessage(JobMessage $msg)
    {
        $msg = clone $msg;
        $msg->context->moveToCurrent($this->jobGraph);
        $msg->input = $msg->output;
        $msg->output = null;

        $index = $this->jobGraph->search($msg->context->getCurrent());

        if ($this->jobGraph->isLoader($index)) {
            if ($msg->pipe) {
                foreach ($msg->pipe->params as $pipe) {
                    $forward = clone $msg;
                    $forward->context->initOptions();
                    $forward->pipe = $pipe;
                    $graph = clone $this->jobGraph;
                    $forward->context->updateToNextJob($graph);
                    $this->addMessage($forward);
                }
            } else {
                $msg->context->tick();

                if (!$msg->context->isFinished()) {
                    $msg->context->addStep($msg->context->getCurrent());
                    $next = $this->jobGraph->getExtractor($index);
                    $msg->context->setCurrent($next);
                } else {
                    $msg = null;
                }
            }
        } elseif (!$this->jobGraph->hasNextJob() && $msg->context->isFinished()) {
            $msg = null;
        } else {
            $msg->context->updateToNextJob($this->jobGraph);
        }

        if (null !== $msg) {
            $this->addMessage($msg);
        }

        return $this;
    } 

    /**
     * Executes current job.
     * Injects ExecutionContext as Visitor
     *
     * @param JobMessage $msg
     */
    protected function runJob(JobMessage $msg)
    {
        // Store input message
        $this->startMsg = $msg;

        $context = new ExecutionContext(
            $this->startMsg,
            $this->jobGraph
        );

        if (null !== $this->logger) {
            $context->setLogger($this->logger);
        }

        $output = $context->executeJob($this->job);

        // Event ? To handle createEndMsg in a more readable way ?
        if (!$output instanceof JobOutput) {
            return $output;
        }

        $end = $this->createEndMsg($output);

        return $end;
    }

    /**
     * @return JobMessage
     */
    private function getInitMessage()
    {
        return new JobMessage(
            new JobContext(
                $this->getJob()->getName(),
                $this->getJob()->getOption('context')
            )
        );
    }

    /**
     * @param JobOutput $output
     *
     * @return JobMessage
     */
    private function createEndMsg(JobOutput $output)
    {
        $msg = clone $this->startMsg;
        
        $msg->output = $output->getData();
        $msg->pipe = $output->getPipe();

        return $msg;
    }

    /**
     * Build a graph on children execution order
     */
    private function buildGraph()
    {
        $children = $this->getJob()->getChildren();

        $this->jobGraph = new JobGraph(new \ArrayIterator(array_keys($children)));
    }
}