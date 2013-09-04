<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Transformer;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class CallbackTransformerType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Transformer\CallbackTransformer',
            'etl_config' => function(Options $options) {
                $class = $options['class'];

                return array(
                    'transformer' => new $class($options['callback']),
                );
            }
        ));

        $resolver->setRequired(array(
            'callback'
        ));

        $resolver->setAllowedTypes(array(
            'callback' => 'callable'
        ));
    }

    public function getName()
    {
        return 'callback_transformer';
    }

    public function getParent()
    {
        return 'transformer';
    }
}