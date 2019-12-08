<?php


namespace Ecotone\JMSConverter;


use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Metadata\Driver\DriverInterface;

class ClassDefinitionDriverFactory implements DriverFactoryInterface
{
    /**
     * @var PropertyNamingStrategyInterface
     */
    private $propertyNamingStrategy;

    public function __construct(PropertyNamingStrategyInterface $propertyNamingStrategy)
    {
        $this->propertyNamingStrategy = $propertyNamingStrategy;
    }

    public function createDriver(array $metadataDirs, Reader $annotationReader): DriverInterface
    {
        return new ClassDefinitionDriver(new AnnotationDriver($annotationReader, $this->propertyNamingStrategy));
    }
}