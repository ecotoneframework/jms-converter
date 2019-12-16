<?php


namespace Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert;


class PropertyWithNullUnionType
{
    /**
     * @var null|string
     */
    private $data;

    /**
     * PropertyWithNullUnionType constructor.
     * @param string|null $data
     */
    public function __construct(?string $data)
    {
        $this->data = $data;
    }
}