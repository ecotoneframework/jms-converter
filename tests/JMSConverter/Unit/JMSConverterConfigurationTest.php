<?php


namespace Test\Ecotone\JMSConverter\Unit;

use Ecotone\AnnotationFinder\InMemory\InMemoryAnnotationFinder;
use Ecotone\JMSConverter\Configuration\JMSConverterConfigurationModule;
use Ecotone\JMSConverter\JMSConverterBuilder;
use Ecotone\JMSConverter\JMSConverterConfiguration;
use Ecotone\JMSConverter\JMSHandlerAdapter;
use Ecotone\Messaging\Config\Annotation\InMemoryAnnotationRegistrationService;
use Ecotone\Messaging\Config\ApplicationConfiguration;
use Ecotone\Messaging\Config\InMemoryModuleMessaging;
use Ecotone\Messaging\Config\MessagingSystemConfiguration;
use Ecotone\Messaging\Config\ModuleReferenceSearchService;
use Ecotone\Messaging\Handler\TypeDescriptor;
use PHPUnit\Framework\TestCase;
use stdClass;
use Test\Ecotone\JMSConverter\Fixture\Configuration\ArrayConversion\ArrayToArrayConverter;
use Test\Ecotone\JMSConverter\Fixture\Configuration\ArrayConversion\ClassToArrayConverter;
use Test\Ecotone\JMSConverter\Fixture\Configuration\ClassToClass\ClassToClassConverter;
use Test\Ecotone\JMSConverter\Fixture\Configuration\SimpleTypeToSimpleType\SimpleTypeToSimpleType;
use Test\Ecotone\JMSConverter\Fixture\Configuration\Status\Status;
use Test\Ecotone\JMSConverter\Fixture\Configuration\Status\StatusConverter;

class JMSConverterConfigurationTest extends TestCase
{
    public function test_registering_converter_and_convert()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(
            InMemoryAnnotationFinder::createFrom([StatusConverter::class])
        );

        $configuration = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $annotationConfiguration->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(
                    new JMSConverterBuilder(
                        [
                            JMSHandlerAdapter::create(
                                TypeDescriptor::create(Status::class),
                                TypeDescriptor::createStringType(),
                                StatusConverter::class,
                                "convertFrom"
                            ),
                            JMSHandlerAdapter::create(
                                TypeDescriptor::createStringType(),
                                TypeDescriptor::create(Status::class),
                                StatusConverter::class,
                                "convertTo"
                            )
                        ], JMSConverterConfiguration::createWithDefaults(), null
                    )
                ),
            $configuration,
        );
    }

    public function test_not_registering_converter_from_simple_type_to_simple_type()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(
            InMemoryAnnotationFinder::createFrom([SimpleTypeToSimpleType::class])
        );

        $configuration = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $annotationConfiguration->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(new JMSConverterBuilder([], JMSConverterConfiguration::createWithDefaults(), null)),
            $configuration,
        );
    }

    public function test_always_registering_with_cache()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(
            InMemoryAnnotationFinder::createFrom([SimpleTypeToSimpleType::class])
        );

        $configuration            = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $applicationConfiguration = ApplicationConfiguration::createWithDefaults()
            ->withCacheDirectoryPath("/tmp")
            ->withEnvironment("dev");
        $annotationConfiguration->prepare($configuration, [$applicationConfiguration], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(new JMSConverterBuilder([], JMSConverterConfiguration::createWithDefaults(), "/tmp")),
            $configuration,
        );
    }

    public function test_not_registering_converter_from_class_to_class()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(
            InMemoryAnnotationFinder::createFrom([ClassToClassConverter::class])
        );

        $configuration = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $annotationConfiguration->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(new JMSConverterBuilder([], JMSConverterConfiguration::createWithDefaults(), null)),
            $configuration,
        );
    }

    public function test_not_registering_converter_from_iterable_to_iterable()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(
            InMemoryAnnotationFinder::createFrom([ArrayToArrayConverter::class])
        );

        $configuration = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $annotationConfiguration->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(new JMSConverterBuilder([], JMSConverterConfiguration::createWithDefaults(), null)),
            $configuration,
        );
    }

    public function test_registering_converter_from_array_to_class()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(
            InMemoryAnnotationFinder::createFrom([ClassToArrayConverter::class])
        );

        $configuration = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $annotationConfiguration->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(
                    new JMSConverterBuilder(
                        [
                            JMSHandlerAdapter::create(
                                TypeDescriptor::createArrayType(),
                                TypeDescriptor::create(stdClass::class),
                                ClassToArrayConverter::class,
                                "convertFrom"
                            ),
                            JMSHandlerAdapter::create(
                                TypeDescriptor::create(stdClass::class),
                                TypeDescriptor::createArrayType(),
                                ClassToArrayConverter::class,
                                "convertTo"
                            )
                        ], JMSConverterConfiguration::createWithDefaults(), null
                    )
                ),
            $configuration,
        );
    }

    public function test_configuring_with_different_options()
    {
        $annotationConfiguration = JMSConverterConfigurationModule::create(InMemoryAnnotationFinder::createEmpty());

        $configuration = MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty());
        $annotationConfiguration->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $this->assertEquals(
            MessagingSystemConfiguration::prepareWithDefaults(InMemoryModuleMessaging::createEmpty())
                ->registerConverter(
                    new JMSConverterBuilder([], JMSConverterConfiguration::createWithDefaults(), null)
                ),
            $configuration,
        );
    }
}