<?php

namespace Rezzza\Jobflow\Extension\ETL\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Extension\ETL\Processor\ETLConfigProcessor;
use Rezzza\Jobflow\JobBuilder;

/**
 * For all type based on ETL pattern we need to specify the step of the process
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class ETLType extends AbstractJobType
{
    const TYPE_EXTRACTOR = 'extractor';
    const TYPE_TRANSFORMER = 'transformer';
    const TYPE_LOADER = 'loader';

    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->setETLType($this->getETLType())
        ;
    }

    public function buildConfig($config, $options)
    {
        $config
            ->setConfigProcessor($options['config_processor'])
        ;
    }

    abstract function getETLType();

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'class'
        ));

        $resolver->setDefaults(array(
            'args' => array(),
            'config_processor' => function(Options $options) {
                return new ETLConfigProcessor(
                    $options['class'],
                    $options['args'],
                    $this->getETLType()
                );
            } 
        ));
    }

    protected function isLoggable($object)
    {
        if (!is_object($object)) {
            return false;
        }

        return in_array('setLogger', get_class_methods(get_class($object)));
    }
}