<?php

namespace Rezzza\Jobflow\Metadata;

use Symfony\Component\PropertyAccess\PropertyAccess;

class MetadataAccessor
{
    private $writeMapping;

    private $readMapping;

    private $accessor;

    public function __construct(array $read = array(), array $write = array())
    {
        $this->readMapping = $read;
        $this->writeMapping = $write;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function createMetadata($result, $metadata = null)
    {
        if (null == $metadata) {
            $metadata = new Metadata();
        }

        foreach ($this->writeMapping as $k => $v) {
            $metadata[$k] = $this->accessor->getValue($result, $v);
        }

        return $metadata;
    }

    public function read($metadata, &$target)
    {
        foreach ($this->readMapping as $k => $v) {
            $this->accessor->setValue($target, $k, $metadata[$v]);
        }
    }
}