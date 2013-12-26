<?php

namespace Rezzza\Jobflow\Scheduler;

use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobContext;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\JobPayload;
use Rezzza\Jobflow\JobData;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Extension\Pipe\PipeData;

/**
 * Wraps job execution around current context
 */
class ExecutionContext
{
    protected $job;

    protected $jobGraph;

    protected $jobContext;

    protected $input;

    protected $output;

    protected $pipe;

    protected $terminated = false;

    public function __construct(JobInterface $job)
    {
        $this->job = $job;
        $this->output = new JobPayload();

        $this->initPipe();
        $this->buildGraph();
    }

    public function execute(JobMessage $msg, JobMessageFactory $msgFactory)
    {
        $child = $this
            ->start($msg)
            ->currentChild()
        ;

        $child->execute($this);

        return $msgFactory->createMsg($this->jobContext, $this->output);
    }

    public function tick()
    {
        $this->jobContext->tick();
    }

    public function initContext(JobContext $context)
    {
        $this->jobContext = $context;
        $this->jobGraph->move($context->getCurrent());
    }

    public function initInput(JobPayload $payload)
    {
        $this->input = $payload;
    }

    public function initOutput(JobPayload $payload)
    {
        $this->output = $payload;
    }

    public function read()
    {
        return $this->input;
    }

    public function write($result, $metadata = null)
    {
        $this->output->store(new JobData($result, $metadata));
    }

    public function start($msg)
    {
        $msg->initExecutionContext($this);
        $msg->initExecutionInput($this);
        $this->output = new JobPayload();

        return $this;
    }

    public function end($msg)
    {
        $msg->initExecutionContext($this);
        $msg->initExecutionOutput($this);

        return $this;
    }

    public function createInitMsgs($msgFactory, $io)
    {
        $stdin = $io ? $io->getStdin() : null;
        $stdout = $io ? $io->getStdout() : null;

        $inputs = $this->buildInputs($stdin, $stdout);

        return $msgFactory->createInitMsgs(
            $this->createJobContexts($inputs, $this->jobGraph->current())
        );
    }

    public function createPipeMsgs($msgFactory)
    {
        $stdout = null;
        $msgs = [];

        if ($this->getIo()) {
            $stdout = $this->getIo()->getStdout();
        }

        foreach ($this->output as $data) {
            if ($data->getValue() instanceof Io\Input) {
                $io = new Io\IoDescriptor($data->getValue(), $stdout);

                $context = new JobContext(
                    $this->job->getName(),
                    $io,
                    $this->jobGraph->getNextJob(),
                    $this->job->getConfig()->getOption('context', []),
                    $this->job->getOptions()
                );

                $payload = new JobPayload([new PipeData(null, $data->getMetadata())]);
                $msgs[] = $msgFactory->createMsg($context, $payload);
            }
        }

        return $msgs;
    }

    public function createNextMsg($msgFactory)
    {
        $next = $this->jobGraph->getNextJob();

        if ($next) {
            $this->jobContext->moveTo($next);
        } else {
            $this->jobContext->reset();
        }

        return $msgFactory->createMsg($this->jobContext, $this->output);
    }

    public function createResetMsg($msgFactory)
    {
        $this->jobContext->reset();

        return $msgFactory->createMsg($this->jobContext, new JobPayload());
    }

    public function rewindData()
    {
        $this->output = $this->input;
    }

    public function currentChild()
    {
        return $this->job->get($this->jobContext->getCurrent());
    }

    public function hasNextJob()
    {
        return $this->jobGraph->hasNextJob();
    }

    public function isFinished()
    {
        return true == $this->terminated || (is_integer($this->getContextOption('total')) && $this->getContextOption('total') <= $this->getContextOption('offset'));
    }

    public function terminate()
    {
        $this->terminated = true;
    }

    public function logState($logger)
    {
        if (!$logger) {
            return;
        }

        $logger->info(sprintf(
                '[%s] [%s] : Read message for job',
                $this->jobContext->jobId,
                $this->jobContext->getCurrent()
            ),
            $this->jobContext->getOptions()
        );
    }

    public function setContextOption($key, $value)
    {
        return $this->jobContext->setOption($key, $value);
    }

    public function getContextOption($name)
    {
        return $this->jobContext->getOption($name);
    }

    public function getJobOption($name, $default = null)
    {
        return $this->currentChild()->getOption($name, $default);
    }

    public function getLogger()
    {
        return $this->currentChild()->getConfig()->getAttribute('logger');
    }

    public function getIo()
    {
        return $this->jobContext->getIo();
    }

    protected function createJobContexts($inputs, $current)
    {
        $contexts = [];

        foreach ($inputs as $input) {
            $contexts[] = new JobContext(
                $this->job->getName(),
                $input,
                $current,
                $this->job->getConfig()->getOption('context', []),
                $this->job->getOptions()
            );
        }

        return $contexts;
    }

    protected function buildInputs($stdin, $stdout)
    {
        $inputs = [];

        if (null === $stdin) {
            // If no IO defined, we want to keep the loop over results of this method.
            // So we return explicitely an array with only null value
            return [null];
        }

        if ($stdin instanceof \Traversable) {
            foreach ($stdin as $input) {
                $inputs[] = new Io\IoDescriptor($input, $stdout);
            }
        } else {
            $inputs[] = new Io\IoDescriptor($stdin, $stdout);
        }

        return $inputs;
    }

    protected function buildGraph()
    {
        $children = $this->job->getChildren();
        $this->jobGraph = new JobGraph(new \ArrayIterator(array_keys($children)));
    }

    protected function initPipe()
    {
        $this->pipe = new Io\InputAggregator;
    }
}