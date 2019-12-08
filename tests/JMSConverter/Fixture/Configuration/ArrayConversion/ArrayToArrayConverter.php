<?php


namespace Test\Ecotone\JMSConverter\Fixture\Configuration\ArrayConversion;


use Ecotone\Messaging\Annotation\Converter;
use Ecotone\Messaging\Annotation\MessageEndpoint;

/**
 * @MessageEndpoint()
 */
class ArrayToArrayConverter
{
    /**
     * @Converter()
     */
    public function convert(array $data) : array
    {

    }
}