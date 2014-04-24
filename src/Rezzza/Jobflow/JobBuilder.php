<?php

namespace Rezzza\Jobflow;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Make Job creation easier, better, stronger
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobBuilder extends JobConfig
{
    /**
     * @var array
     */
    protected $children = array();

    /**
     * @var array
     */
    protected $unresolvedChildren = array();

    /**
     * @param string $name
     * @param JobFactory $jobFactory
     */
    public function __construct($name, JobFactory $jobFactory, EventDispatcherInterface $dispatcher, array $initOptions = array(), array $execOptions = array())
    {
        parent::__construct($name, $dispatcher, $initOptions, $execOptions);
        $this->setJobFactory($jobFactory);
    }

    /**
     * Add sub job to a job
     *
     * @param string $child Name of the child
     * @param string $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $execOptions
     * @param array $initOptions
     *
     * @return JobBuilder
     */
    public function add($child, $type, array $execOptions = array(), array $initOptions = array())
    {
        if (!is_string($child) && !is_int($child)) {
            throw new \InvalidArgumentException(sprintf('child name should be string or, integer'));
        }

        if (null !== $type && !is_string($type) && !$type instanceof JobTypeinterface) {
            throw new \InvalidArgumentException('type should be string or JobTypeinterface');
        }

        $this->children[$child] = null; // to keep order
        $this->unresolvedChildren[$child] = array(
            'type' => $type,
            'init_options' => $initOptions,
            'exec_options' => $execOptions
        );

        return $this;
    }

    /**
     * @param string $name
     */
    public function has($name)
    {
        return isset($this->unresolvedChildren[$name]) 
            || isset($this->children[$name]);
    }

    /**
     * Create new JobBuilder
     *
     * @param string $name
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     *
     * @return JobBuilder
     */
    public function create($name, $type = null, array $initOptions = array(), array $execOptions = array())
    {
        if (null === $type) {
            $type = 'job';
        }

        return $this->getJobFactory()->createNamedBuilder($name, $type, $initOptions, $execOptions);
    }

    /**
     * Create the job with all children configure
     *
     * @return Job
     */
    public function getJob()
    {
        $this->resolveChildren();

        $job = new Job($this->getJobConfig());

        foreach ($this->children as $child) {
            $job->add($child->getJob());
        }

        return $job;
    }

    public function hasUnresolvedChildren()
    {
        return count($this->unresolvedChildren) > 0;
    }

    /**
     * For each child added, we create a new JobBuilder around it to make fully configurable each sub job
     */
    protected function resolveChildren()
    {
        foreach ($this->unresolvedChildren as $name => $info) {
            $this->children[$name] = $this->create($name, $info['type'], $info['init_options'], $info['exec_options']);
        }

        $this->unresolvedChildren = array();
    }
}