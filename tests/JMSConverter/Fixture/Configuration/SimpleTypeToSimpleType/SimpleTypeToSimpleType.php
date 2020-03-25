<?php


namespace Test\Ecotone\JMSConverter\Fixture\Configuration\SimpleTypeToSimpleType;

use Ecotone\Messaging\Annotation\Converter;
use Ecotone\Messaging\Annotation\ConverterClass;

/**
 * @ConverterClass()
 */
class SimpleTypeToSimpleType
{
    /**
     * @Converter()
     */
    public function convert(string $type): string
    {

    }
}