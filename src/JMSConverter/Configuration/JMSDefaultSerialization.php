<?php

namespace Ecotone\JMSConverter\Configuration;

use Ecotone\Messaging\Annotation\ApplicationContext;
use Ecotone\Messaging\Annotation\Extension;
use Ecotone\Messaging\Config\ApplicationConfiguration;
use Ecotone\Messaging\Conversion\MediaType;

/**
 * Class JMSConverterConfiguration
 * @package Ecotone\JMSConverter
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @ApplicationContext()
 */
class JMSDefaultSerialization
{
    /**
     * @Extension()
     */
    public function getDefaultConfig(): ApplicationConfiguration
    {
        return ApplicationConfiguration::createWithDefaults()
            ->withDefaultSerializationMediaType(MediaType::APPLICATION_JSON);
    }
}