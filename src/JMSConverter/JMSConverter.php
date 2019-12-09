<?php


namespace Ecotone\JMSConverter;

use Ecotone\Messaging\Conversion\Converter;
use Ecotone\Messaging\Conversion\MediaType;
use Ecotone\Messaging\Handler\TypeDescriptor;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;

class JMSConverter implements Converter
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function convert($source, TypeDescriptor $sourceType, MediaType $sourceMediaType, TypeDescriptor $targetType, MediaType $targetMediaType)
    {
        if ($sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_X_PHP) && $targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_X_PHP)) {
            if ($sourceType->isIterable()) {
                return $this->serializer->fromArray($source, $targetType->toString());
            }else if ($targetType->isIterable()) {
                return $this->serializer->toArray($source);
            }else {
                throw new \InvalidArgumentException("Can't conversion from {$sourceMediaType->toString()}:{$sourceType->toString()} to {$targetMediaType->toString()}:{$targetMediaType->toString()}");
            }
        }

        if ($targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_X_PHP)) {
            if ($sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_JSON)) {
                return $this->serializer->deserialize($source, $targetType->toString(), "json");
            }else if ($sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_XML)) {
                return $this->serializer->deserialize($source, $targetType->toString(), "xml");
            }else {
                throw new \InvalidArgumentException("Can't conversion from {$sourceMediaType->toString()}:{$sourceType->toString()} to {$targetMediaType->toString()}:{$targetMediaType->toString()}");
            }
        } else {
            if ($targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_JSON)) {
                return $this->serializer->serialize($source, "json");
            }else if ($targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_XML)) {
                return $this->serializer->serialize($source, "xml");
            }else {
                throw new \InvalidArgumentException("Can't conversion from {$sourceMediaType->toString()}:{$sourceType->toString()} to {$targetMediaType->toString()}:{$targetMediaType->toString()}");
            }
        }
    }

    public function matches(TypeDescriptor $sourceType, MediaType $sourceMediaType, TypeDescriptor $targetType, MediaType $targetMediaType): bool
    {
        if ($sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_X_PHP) && $targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_X_PHP)) {
            return $sourceType->isIterable() || $targetType->isIterable();
        }

        if (!$sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_JSON) && !$sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_XML)
            && !$targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_JSON) && !$targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_XML)
        ) {
            return false;
        }

        if ($sourceType->isInterface() || $targetType->isInterface()) {
            return false;
        }

        return true;
    }
}