<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Extension;

/**
 * For standalone use
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
final class Jobs
{
    /**
     * Creates a job factory with the default configuration.
     *
     * @return JobFactory The job factory.
     */
    public static function createJobFactory()
    {
        return self::createJobsBuilder()->getJobFactory();
    }

    public static function createJobflowFactory()
    {
        return self::createJobsBuilder()->getJobflowFactory();
    }

    /**
     * Creates a form factory builder with the default configuration.
     *
     * @return JobFactoryBuilder The job factory builder.
     */
    public static function createJobsBuilder()
    {
        $builder = new JobsBuilder();
        $builder->addExtension(new Extension\Core\CoreExtension());
        $builder->addExtension(new Extension\ETL\ETLExtension());

        return $builder;
    }

    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }
}