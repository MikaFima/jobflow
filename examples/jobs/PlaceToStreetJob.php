<?php

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobBuilder;

class PlaceToStreetJob extends AbstractJobType
{
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->add(
                'example_extractor', // name
                'json_extractor',
                array(
                    'path' => 'results'
                )
            )
            ->add(
                'example_transformer', // name
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
                        $img = sprintf(
                            'http://maps.googleapis.com/maps/api/streetview?size=800x600&location=%F,%F&fov=90&heading=235&pitch=10&sensor=false', 
                            $data->geometry->location->lat,
                            $data->geometry->location->lng
                        );

                        return file_get_contents($img);
                    },
                    'metadata_write' => array(
                        'id' => 'place_id' // Store $data->id in metadata in order to reuse it in loader
                    )
                )
            )
            ->add(
                'example_loader',
                'file_loader',
                array(
                    'args' => function(Options $options) {
                        $id = $options['message']->getMetadata('place_id');

                        return array(new \SplFileObject(__DIR__."/../temp/job-".$id.".jpeg", 'w+'));
                    }
                )
            )
        ;
    }

    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'context' => array(
                'limit' => 1
            )
        ));
    }

    public function getName()
    {
        return 'place_to_street';
    }
}