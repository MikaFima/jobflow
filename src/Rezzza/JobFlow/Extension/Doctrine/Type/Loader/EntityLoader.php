<?php

namespace Rezzza\JobFlow\Extension\Doctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class EntityLoader extends AbstractJobType
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine:
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $type = $this;
        $doctrine = $this->doctrine;

        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\Doctrine\ORMLoader',
            'etl_config' => function(Options $options) use ($type, $doctrine) {
                $class = $options['class'];

                return array(
                    'loader' => new $class($doctrine)
                );
            } 
        ));
    }

    public function getName()
    {
        return 'entity_loader';
    }

    public function getParent()
    {
        return 'loader';
    }
}