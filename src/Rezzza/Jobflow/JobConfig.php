<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Io\IoDescriptor;

/**
 * Config the job.
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobConfig 
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ResolvedJob
     */
    private $resolved;

    /**
     * @var array
     */
    private $configProcessor;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options = array())
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @return JobConfig
     */
    public function getJobConfig()
    {
        // This method should be idempotent, so clone the builder
        $config = clone $this;

        return $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    /**
     * @return array
     */
    public function getConfigProcessor()
    {
        return $this->configProcessor;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @return array
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return boolean
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param ResolvedJob $resolved
     *
     * @return JobConfig
     */
    public function setResolved(ResolvedJob $resolved)
    {
        $this->resolved = $resolved;

        return $this;
    }

    /**
     * @param array $etlConfig
     *
     * @return JobConfig
     */
    public function setConfigProcessor($config)
    {
        $this->configProcessor = $config;

        return $this;
    }

    /**
     * @param JobFactory $etlConfig
     *
     * @return JobConfig
     */
    public function setJobFactory(JobFactory $jobFactory)
    {
        $this->jobFactory = $jobFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }
}