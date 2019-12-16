<?php


namespace Ecotone\JMSConverter;


use Ecotone\Messaging\Handler\ClassDefinition;
use Ecotone\Messaging\Handler\ClassPropertyDefinition;
use Ecotone\Messaging\Handler\TypeDescriptor;
use Ecotone\Messaging\Handler\UnionTypeDescriptor;
use Ecotone\Messaging\Support\InvalidArgumentException;
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
            $propertyMetadata->type = $this->toSerializerType($class->getName(), $classDefinition->getProperty($propertyMetadata->name), $propertyMetadata->type);
        }

        return $classMetadata;
    }

    private function toSerializerType(string $className, ClassPropertyDefinition $propertyDefinition, ?array $jmsType) : ?array
    {
        if ($propertyDefinition->hasAnnotation(TypeDescriptor::create(Type::class)) || $propertyDefinition->getType()->isAnything()) {
            return $jmsType;
        }

        /** @var UnionTypeDescriptor|TypeDescriptor $type */
        $type = $propertyDefinition->getType();
        $params = [];

        if ($type->isUnionType()) {
            $types = [];
            foreach ($type->getUnionTypes() as $unionType) {
                if (!$unionType->equals(TypeDescriptor::create(TypeDescriptor::NULL))) {
                    $types[] = $unionType;
                }
            }
            $type = UnionTypeDescriptor::createWith($types);
        }

        if ($type->isUnionType()) {
            throw InvalidArgumentException::create("JMS Converter is not cable of handling union types. Class {$className}::{$propertyDefinition->getName()} is using union type {$propertyDefinition->getType()}");
        }

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