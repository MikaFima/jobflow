<?php

namespace Rezzza\Jobflow\Extension\DI;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Rezzza\Jobflow\Extension\BaseExtension;

class DependencyInjectionExtension extends BaseExtension
{
    private $container;

    private $typeServiceIds;

    private $typeExtensionServiceIds;

    private $transportServiceIds;

    public function __construct(
        ContainerInterface $container,
        array $typeServiceIds, 
        array $typeExtensionServiceIds,
        array $transportServiceIds
    )
    {
        $this->container = $container;
        $this->typeServiceIds = $typeServiceIds;
        $this->typeExtensionServiceIds = $typeExtensionServiceIds;
        $this->transportServiceIds = $transportServiceIds;
    }

    public function getType($name)
    {
        if (!isset($this->typeServiceIds[$name])) {
            throw new InvalidArgumentException(sprintf('The field type "%s" is not registered with the service container.', $name));
        }

        $type = $this->container->get($this->typeServiceIds[$name]);

        if ($type->getName() !== $name) {
            throw new InvalidArgumentException(
                sprintf('The type name specified for the service "%s" does not match the actual name. Expected "%s", given "%s"',
                    $this->typeServiceIds[$name],
                    $name,
                    $type->getName()
                ));
        }

        return $type;
    }

    public function hasType($name)
    {
        return isset($this->typeServiceIds[$name]);
    }

    public function getTypeExtensions($name)
    {
        $extensions = array();
        
        if (isset($this->typeExtensionServiceIds[$name])) {
            foreach ($this->typeExtensionServiceIds[$name] as $serviceId) {
                $extensions[] = $this->container->get($serviceId);
            }
        }

        return $extensions;
    }

    public function hasTypeExtensions($name)
    {
        return isset($this->typeExtensionServiceIds[$name]);
    }

    public function getTransport($name)
    {
        if (!isset($this->transportServiceIds[$name])) {
            throw new InvalidArgumentException(sprintf('The transport "%s" is not registered with the service container.', $name));
        }

        $transport = $this->container->get($this->transportServiceIds[$name]);

        if ($transport->getName() !== $name) {
            throw new InvalidArgumentException(
                sprintf('The transport specified for the service "%s" does not match the actual name. Expected "%s", given "%s"',
                    $this->transportServiceIds[$name],
                    $name,
                    $transport->getName()
                ))
            ;
        }

        return $transport;
    }

    public function hasTransport($name)
    {
        return isset($this->transportServiceIds[$name]);
    }
}