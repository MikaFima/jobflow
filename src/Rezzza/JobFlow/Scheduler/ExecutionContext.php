<?php

namespace Rezzza\JobFlow\Scheduler;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Rezzza\JobFlow\JobInterface;
use Rezzza\JobFlow\JobMessage;

/**
 * Wrap and contextualize execution of job
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class ExecutionContext
{
    use LoggerAwareTrait;

    /**
     * Current msg
     *
     * @var JobMessage
     */
    public $msg;

    /**
     * Global Context moved from message to message
     *
     * @var JobContext
     */
    public $globalContext;

    /**
     * Representation of the navigation through the jobs
     *
     * @var RecursiveArrayIterator
     */
    public $graph;

    /**
     * Current job in execution
     *
     * @var JobInterface
     */
    public $job;

     /**
      * @var array
      */
    protected $options;

    /**
     * @param JobInterface $job
     * @param JobMessage $msg
     * @param JobGraph $graph
     * @param array $options
     */
    public function __construct(JobMessage $msg, JobGraph $graph)
    {
        $this->graph = $graph;
        $this->msg = $msg;
        $this->globalContext = $this->msg->context;
        $this->initCurrentJob();
 
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($this->globalContext->options);
    }

    /**
     * Run execute on a job for the current msg.
     * It will determine himself which child need to be execute
     *
     * @param JobInterface $job
     */
    public function executeJob(JobInterface $parent)
    {
        if (null === $this->getCurrentJob()) {
            // No more Job to run. debug
            return 0;
        }
        
        $this->job = $parent->get($this->getCurrentJob());
        
        return $this->job->execute($this);
    }

    /**
     * Checks if we starts the graph
     *
     * @return boolean
     */
    public function isFirstStep()
    {
        return $this->graph->key() === 0;
    }

    /**
     * Checks if we ends the graph
     *
     * @return boolean
     */
    public function isLastStep()
    {
        return $this->graph->key() === (count($this->graph) - 1);
    }

    /**
     * Get name of the child job in execution
     * 
     * @return string
     */
    public function getCurrentJob()
    {
        return $this->globalContext->current;
    }

    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->globalContext->jobId;
    }

    /**
     * At the begining get the first item of the graph
     */
    public function initCurrentJob()
    {
        if ($this->globalContext->isStarting()) {
            $this->globalContext->current = $this->graph->current();

            return;
        }

        $index = array_search($this->globalContext->current, $this->graph->getArrayCopy());
        $this->graph->seek($index);
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'offset' => null,
            'limit' => null,
            'total' => null
        ));
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setGlobalOption($key, $value)
    {
        $this->globalContext->options[$key] = $value;
    }
}