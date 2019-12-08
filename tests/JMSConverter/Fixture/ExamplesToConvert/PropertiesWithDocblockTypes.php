<?php


namespace Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert;


class PropertiesWithDocblockTypes
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $surname;

    /**
     * ObjectWithDocblockTypes constructor.
     * @param string $name
     * @param string $surname
     */
    public function __construct(string $name, string $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }
}