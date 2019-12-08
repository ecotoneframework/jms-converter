<?php


namespace Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert;


class ThreeLevelNestedObjectProperty
{
    /**
     * @var TwoLevelNestedObjectProperty
     */
    private $data;

    /**
     * TwoLevelNestedObjectProperty constructor.
     * @param TwoLevelNestedObjectProperty $data
     */
    public function __construct(TwoLevelNestedObjectProperty $data)
    {
        $this->data = $data;
    }
}