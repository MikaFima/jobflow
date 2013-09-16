<?php

namespace Rezzza\Jobflow\Extension\ETL;

use Rezzza\Jobflow\Extension\BaseExtension;

class ETLExtension extends BaseExtension
{
    public function loadTypes()
    {
        return array(
            new Type\Extractor\ExtractorType(),
            new Type\Extractor\CsvExtractorType(),
            new Type\Extractor\TsvExtractorType(),
            new Type\Extractor\JsonExtractorType(),

            new Type\Transformer\TransformerType(),
            new Type\Transformer\CallbackTransformerType(),
            new Type\Transformer\DataMapperTransformerType(),
            
            new Type\Loader\LoaderType(),
            new Type\Loader\PipeLoaderType(),
            new Type\Loader\FileLoaderType()
        );
    }
}