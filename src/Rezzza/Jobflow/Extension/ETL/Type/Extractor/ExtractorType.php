<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Knp\ETL;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class ExtractorType extends ETLType
{
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        $extractor = $input->getExtractor();

        if ($this->isLoggable($extractor) && $execution->getLogger()) {
            $extractor->setLogger($execution->getLogger());
        }

        $max = $execution->getGlobalOption('max');

        if (null === $execution->getGlobalOption('total')) {
            $total = $extractor->count();

            if (null !== $max && $max < $total) {
                $total = $max;
            }

            $execution->setGlobalOption('total', $total);
        } else {
            $total = $execution->getGlobalOption('total');
        }

        $offset = $execution->getGlobalOption('offset');
        $limit = $execution->getGlobalOption('limit');

        // Move offset if needed
        if ($execution->getJobOption('offset', 0) > $offset) {
            $offset = $execution->getJobOption('offset');
            $execution->setGlobalOption('offset', $offset);
        }

        try {
            $extractor->seek($offset);
        } catch (\OutOfBoundsException $e) {
            if ($execution->getLogger()) {
                $execution->getLogger()->debug('No data');
            }
        }

        for ($i = 0; $i < $limit && $extractor->valid(); $i++) {
            if ($extractor->key() > $total) {
                break;
            }

            $output->write($extractor->current());
            $extractor->next();
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'offset' => 0,
            'args' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'filename' => $io->stdin->getDsn()
                );
            } 
        ));
    }

    public function getName()
    {
        return 'extractor';
    }

    public function getETLType()
    {
        return self::TYPE_EXTRACTOR;
    }
}