<?php

namespace Rezzza\Jobflow;

/**
 * Store all JobType and Transport availables
 * 
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobRegistry
{
    /**
     * @var JobTypeInterface[]
     */
    protected $types = array();

    /**
     * @var TransportInterface[]
     */
    protected $transports = array();

    /**
     * @var JobExtensionInterface[]
     */
    protected $extensions = array();

    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Look for an extension registred
     *
     * @parameter string $class
     *
     * @return boolean
     */
    public function getExtension($class)
    {
        foreach ($this->extensions as $extension) {
            if (get_class($extension) === $class) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Try to find a JobTypeInterface registered with $name as alias
     *
     * @param string $id
     *
     * @return JobTypeInterface
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            $type = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);
                    break;
                }
            }

            if (!$type) {
                throw new \InvalidArgumentException(sprintf('Could not load type "%s"', $name));
            }

            $this->resolveAndAddType($type);
        }

        return $this->types[$name];
    }

    /**
     * Try to find a IoWrapperInterface registered with $name as alias
     *
     * @param string $id
     *
     * @return JobTypeInterface
     */
    public function getTransport($name)
    {
        if (!isset($this->transports[$name])) {
            $transport = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasTransport($name)) {
                    $transport = $extension->getTransport($name);
                    break;
                }
            }

            if (!$transport) {
                throw new \InvalidArgumentException(sprintf('Could not load transport "%s"', $name));
            }

            $this->transports[$transport->getName()] = $transport;
        }

        return $this->transports[$name];
    }

    /**
     * Wraps a type into a ResolvedFormTypeInterface implementation and connects
     * it with its parent type.
     *
     * @param JobTypeInterface $type The type to resolve.
     *
     * @return ResolvedJob The resolved type.
     */
    private function resolveAndAddType(JobTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof JobTypeInterface) {
            $this->resolveAndAddType($parentType);
            $parentType = $parentType->getName();
        }

        $typeExtensions = array();

        foreach ($this->extensions as $extension) {
            /* @var FormExtensionInterface $extension */
            $typeExtensions = array_merge(
                $typeExtensions,
                $extension->getTypeExtensions($type->getName())
            );
        }

        $this->types[$type->getName()] = new ResolvedJob(
            $type, 
            $typeExtensions,
            $parentType ? $this->getType($parentType) : null
        );
    }
}