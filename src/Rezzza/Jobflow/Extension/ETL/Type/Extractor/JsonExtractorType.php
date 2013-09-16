<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class JsonExtractorType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'path' => null,
            'adapter' => null,
            'class' => 'Knp\ETL\Extractor\JsonExtractor',
            'args' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'dsn' => $io ? $io->stdin->getDsn() : null, 
                    'path' => $options['path'],
                    'adapter' => $options['adapter']
                );
            } 
        ));
    }

    public function getName()
    {
        return 'json_extractor';
    }

    public function getParent()
    {
        return 'extractor';
    }
}
