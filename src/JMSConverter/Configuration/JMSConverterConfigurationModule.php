<?php


namespace Ecotone\JMSConverter\Configuration;


use Ecotone\AnnotationFinder\AnnotationFinder;
use Ecotone\JMSConverter\JMSConverterBuilder;
use Ecotone\JMSConverter\JMSConverterConfiguration;
use Ecotone\JMSConverter\JMSHandlerAdapter;
use Ecotone\Messaging\Annotation\Converter;
use Ecotone\Messaging\Annotation\ConverterClass;
use Ecotone\Messaging\Annotation\ModuleAnnotation;
use Ecotone\Messaging\Config\Annotation\AnnotatedDefinitionReference;
use Ecotone\Messaging\Config\Annotation\AnnotationModule;
use Ecotone\Messaging\Config\Annotation\ModuleConfiguration\NoExternalConfigurationModule;
use Ecotone\Messaging\Config\ApplicationConfiguration;
use Ecotone\Messaging\Config\Configuration;
use Ecotone\Messaging\Config\ModuleReferenceSearchService;
use Ecotone\Messaging\Handler\InterfaceToCall;

#[ModuleAnnotation]
class JMSConverterConfigurationModule extends NoExternalConfigurationModule implements AnnotationModule
{
    /**
     * @var JMSHandlerAdapter[]
     */
    private $jmsHandlerAdapters;

    /**
     * JMSConverterConfiguration constructor.
     * @param JMSHandlerAdapter[] $jmsHandlerAdapters
     */
    public function __construct(array $jmsHandlerAdapters)
    {
        $this->jmsHandlerAdapters = $jmsHandlerAdapters;
    }


    public static function create(AnnotationFinder $annotationRegistrationService): static
    {
        $registrations = $annotationRegistrationService->findAnnotatedMethods(Converter::class);

        $converters = [];
        foreach ($registrations as $registration) {
            $reference = AnnotatedDefinitionReference::getReferenceFor($registration);
            $interfaceToCall = InterfaceToCall::create($registration->getClassName(), $registration->getMethodName());
            $fromType = $interfaceToCall->getFirstParameter()->getTypeDescriptor();
            $toType = $interfaceToCall->getReturnType();

            if (!$fromType->isClassOrInterface() && !$toType->isClassOrInterface()) {
                continue;
            }
            if ($fromType->isClassOrInterface() && $toType->isClassOrInterface()) {
                continue;
            }

            $converters[] = JMSHandlerAdapter::create(
                $fromType,
                $toType,
                $reference,
                $registration->getMethodName(),
            );
        }

        return new self($converters);
    }

    public function prepare(Configuration $configuration, array $extensionObjects, ModuleReferenceSearchService $moduleReferenceSearchService): void
    {
        $jmsConverterConfiguration = JMSConverterConfiguration::createWithDefaults();
        foreach ($extensionObjects as $extensionObject) {
            if ($extensionObject instanceof JMSConverterConfiguration) {
                $jmsConverterConfiguration = $extensionObject;
            }
        }
        $cacheDirectoryPath = null;
        foreach ($extensionObjects as $extensionObject) {
            if ($extensionObject instanceof ApplicationConfiguration) {
                $cacheDirectoryPath = $extensionObject->getCacheDirectoryPath();
            }
        }

        $configuration->registerConverter(new JMSConverterBuilder($this->jmsHandlerAdapters, $jmsConverterConfiguration, $cacheDirectoryPath));
    }

    public function canHandle($extensionObject): bool
    {
        return $extensionObject instanceof ApplicationConfiguration
               || $extensionObject instanceof JMSConverterConfiguration;
    }
}