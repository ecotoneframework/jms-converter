<?php


namespace Ecotone\JMSConverter;


use Ecotone\Messaging\Handler\ClassDefinition;
use Ecotone\Messaging\Handler\ClassPropertyDefinition;
use Ecotone\Messaging\Handler\TypeDescriptor;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;

class ClassDefinitionDriver implements DriverInterface
{
    /**
     * @var AnnotationDriver
     */
    private $annotationDriver;

    public function __construct(AnnotationDriver $annotationDriver)
    {
        $this->annotationDriver = $annotationDriver;
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
    {
        $classMetadata = $this->annotationDriver->loadMetadataForClass($class);

        $classDefinition = ClassDefinition::createFor(TypeDescriptor::create($class->getName()));

        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            $propertyMetadata->type = $this->toSerializerType($classDefinition->getProperty($propertyMetadata->name), $propertyMetadata->type);
        }

        return $classMetadata;
    }

    private function toSerializerType(ClassPropertyDefinition $propertyDefinition, ?array $jmsType) : ?array
    {
        if ($propertyDefinition->hasAnnotation(TypeDescriptor::create(Type::class)) || $propertyDefinition->getType()->isUnknown()) {
            return $jmsType;
        }

        $type = $propertyDefinition->getType();
        $params = [];

        if ($type->isCollection()) {
            foreach ($type->resolveGenericTypes() as $genericType) {
                $params[] = [
                    "name" => $genericType->toString(),
                    "params" => []
                ];
            }
        }

        return [
            "name" => $type->isCollection() ? TypeDescriptor::ARRAY : $type->toString(),
            "params" => $params
        ];
    }
}