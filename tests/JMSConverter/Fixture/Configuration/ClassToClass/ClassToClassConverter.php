<?php


namespace Test\Ecotone\JMSConverter\Fixture\Configuration\ClassToClass;

use Ecotone\Messaging\Annotation\Converter;
use Ecotone\Messaging\Annotation\MessageEndpoint;

class ClassToClassConverter
{
    #[Converter]
    public function convert(\stdClass $stdClass) : \stdClass
    {

    }
}