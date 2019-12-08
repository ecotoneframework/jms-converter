<?php


namespace Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert;


class TwoLevelNestedObjectProperty
{
    /**
     * @var PropertyWithTypeAndMetadataType
     */
    private $data;

    /**
     * TwoLevelNestedObjectProperty constructor.
     * @param PropertyWithTypeAndMetadataType $data
     */
    public function __construct(PropertyWithTypeAndMetadataType $data)
    {
        $this->data = $data;
    }
}