<?php


namespace Ecotone\JMSConverter;

/**
 * Class JMSConverterConfiguration
 * @package Ecotone\JMSConverter\Configuration
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class JMSConverterConfiguration
{
    const IDENTICAL_PROPERTY_NAMING_STRATEGY = "identicalPropertyNamingStrategy";

    /**
     * @var string
     */
    private $namingStrategy = self::IDENTICAL_PROPERTY_NAMING_STRATEGY;

    private function __construct()
    {
    }

    public static function createWithDefaults()
    {
        return new self();
    }

    /**
     * @param string $namingStrategy
     * @return $this
     */
    public function withNamingStrategy(string $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamingStrategy(): string
    {
        return $this->namingStrategy;
    }
}