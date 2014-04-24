<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Rezzza\JobFlow\JobBuilder;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobTypeExtensionInterface
{
    /**
     * Builds the job.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type.
     *
     * @param JobBuilder $builder The job builder
     * @param array $options The options
     * @return void
     */
    public function buildJob(JobBuilder $builder, array $options);

    /**
     * Sets the init options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     * @return void
     */
    public function setInitOptions(OptionsResolverInterface $resolver);

    /**
     * Sets the exec options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     * @return void
     */
    public function setExecOptions(OptionsResolverInterface $resolver);

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getExtendedType();
}
