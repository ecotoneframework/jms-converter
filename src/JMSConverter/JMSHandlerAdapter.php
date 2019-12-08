<?php


namespace Ecotone\JMSConverter;


use Closure;
use Ecotone\Messaging\Handler\ReferenceSearchService;
use Ecotone\Messaging\Handler\TypeDescriptor;
use Ecotone\Messaging\Support\Assert;
use JMS\Serializer\GraphNavigator;

class JMSHandlerAdapter
{
    /**
     * @var TypeDescriptor
     */
    private $fromType;
    /**
     * @var TypeDescriptor
     */
    private $toType;
    /**
     * @var string
     */
    private $referenceName;
    /**
     * @var string
     */
    private $methodName;

    public function __construct(TypeDescriptor $fromType, TypeDescriptor $toType, string $referenceName, string $methodName)
    {
        Assert::isTrue($fromType->isClass() || $toType->isClass(), "Atleast one side of converter must be class");
        Assert::isFalse($fromType->isClass() && $toType->isClass(), "Both sides of converter cannot to be classes");

        $this->fromType = $fromType;
        $this->toType = $toType;

        $this->referenceName = $referenceName;
        $this->methodName = $methodName;
    }

    public static function create(TypeDescriptor $fromType, TypeDescriptor $toType, string $referenceName, string $methodName) : self
    {
        return new self($fromType, $toType, $referenceName, $methodName);
    }

    public function getSerializerClosure(ReferenceSearchService $referenceSearchService): Closure
    {
        $object = $referenceSearchService->get($this->referenceName);

        return function ($visitor, $data) use ($object) {
            return call_user_func([$object, $this->methodName], $data);
        };
    }

    public function getRelatedClass(): string
    {
        return $this->fromType->isClass() ? $this->fromType->toString() : $this->toType->toString();
    }

    public function getDirection(): int
    {
        return $this->fromType->isClass() ? GraphNavigator::DIRECTION_SERIALIZATION : GraphNavigator::DIRECTION_DESERIALIZATION;
    }
}