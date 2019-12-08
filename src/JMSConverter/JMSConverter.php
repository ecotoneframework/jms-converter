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
        if ($targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_X_PHP)) {
            $format = $sourceMediaType->isCompatibleWithParsed(MediaType::APPLICATION_JSON) ? "json" : "xml";

            return $this->serializer->deserialize($source, $targetType->toString(), $format);
        } else {
            $format = $targetMediaType->isCompatibleWithParsed(MediaType::APPLICATION_JSON) ? "json" : "xml";

            return $this->serializer->serialize($source, $format);
        }
    }

    public function matches(TypeDescriptor $sourceType, MediaType $sourceMediaType, TypeDescriptor $targetType, MediaType $targetMediaType): bool
    {
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