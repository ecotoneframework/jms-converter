<?php

namespace Ecotone\JMSConverter\Configuration;

use Ecotone\Messaging\Annotation\ApplicationContext;
use Ecotone\Messaging\Config\ApplicationConfiguration;
use Ecotone\Messaging\Conversion\MediaType;

class JMSDefaultSerialization
{
    #[ApplicationContext]
    public function getDefaultConfig(): ApplicationConfiguration
    {
        return ApplicationConfiguration::createWithDefaults()
            ->withDefaultSerializationMediaType(MediaType::APPLICATION_JSON);
    }
}