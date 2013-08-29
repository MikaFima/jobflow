<?php

namespace Rezzza\JobFlow\Extension\Core;

use Rezzza\JobFlow\Extension\BaseExtension;

class CoreExtension extends BaseExtension
{
    public function loadTypes()
    {
        return array(
            new Type\JobType(),
            new Type\Extractor\ExtractorType(),
            new Type\Transformer\TransformerType(),
            new Type\Loader\LoaderType()
        );
    }

    public function loadWrappers()
    {
        return array(
            new Wrapper\JobWrapper(),
            new Wrapper\CsvWrapper(),
            new Wrapper\TsvWrapper(),
            new Wrapper\JsonWrapper()
        );
    }
}