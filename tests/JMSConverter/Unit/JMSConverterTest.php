<?php


namespace Test\Ecotone\JMSConverter\Unit;


use Ecotone\JMSConverter\JMSConverterBuilder;
use Ecotone\JMSConverter\JMSConverterConfiguration;
use Ecotone\JMSConverter\JMSHandlerAdapter;
use Ecotone\Messaging\Conversion\Converter;
use Ecotone\Messaging\Conversion\MediaType;
use Ecotone\Messaging\Handler\InMemoryReferenceSearchService;
use Ecotone\Messaging\Handler\TypeDescriptor;
use Ecotone\Messaging\Support\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Test\Ecotone\JMSConverter\Fixture\Configuration\ArrayConversion\ClassToArrayConverter;
use Test\Ecotone\JMSConverter\Fixture\Configuration\Status\Person;
use Test\Ecotone\JMSConverter\Fixture\Configuration\Status\Status;
use Test\Ecotone\JMSConverter\Fixture\Configuration\Status\StatusConverter;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\CollectionProperty;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\PersonInterface;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\PropertiesWithDocblockTypes;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\PropertyWithAnnotationMetadataDefined;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\PropertyWithNullUnionType;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\PropertyWithTypeAndMetadataType;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\PropertyWithUnionType;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\ThreeLevelNestedObjectProperty;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\TwoLevelNestedCollectionProperty;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\TwoLevelNestedObjectProperty;
use Test\Ecotone\JMSConverter\Fixture\ExamplesToConvert\TypedProperty;

class JMSConverterTest extends TestCase
{
    public function test_converting_with_docblock_types()
    {
        $toSerialize = new PropertiesWithDocblockTypes("Johny", "Silverhand");
        $expectedSerializationString = '{"name":"Johny","surname":"Silverhand"}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_converting_with_annotation_docblock()
    {
        $toSerialize = new PropertyWithAnnotationMetadataDefined("Johny");
        $expectedSerializationString = '{"naming":"Johny"}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_overriding_type_with_metadata()
    {
        $toSerialize = new PropertyWithTypeAndMetadataType(5);
        $expectedSerializationString = '{"data":5}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    /**
     * @requires PHP >= 7.4
     */
    public function test_converting_with_typed_property()
    {
        $toSerialize = new TypedProperty(3);
        $expectedSerializationString = '{"data":3}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_two_level_object_nesting()
    {
        $toSerialize = new TwoLevelNestedObjectProperty(new PropertyWithTypeAndMetadataType(3));
        $expectedSerializationString = '{"data":{"data":3}}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_three_level_object_nesting()
    {
        $toSerialize = new ThreeLevelNestedObjectProperty(new TwoLevelNestedObjectProperty(new PropertyWithTypeAndMetadataType(3)));
        $expectedSerializationString = '{"data":{"data":{"data":3}}}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_with_collection_type()
    {
        $toSerialize = new CollectionProperty([new PropertyWithTypeAndMetadataType(3), new PropertyWithTypeAndMetadataType(4)]);
        $expectedSerializationString = '{"collection":[{"data":3},{"data":4}]}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_with_nested_collection_type()
    {
        $toSerialize = new TwoLevelNestedCollectionProperty([
            new CollectionProperty([new PropertyWithTypeAndMetadataType(1), new PropertyWithTypeAndMetadataType(2)]),
            new CollectionProperty([new PropertyWithTypeAndMetadataType(3), new PropertyWithTypeAndMetadataType(4)])
        ]);
        $expectedSerializationString = '{"collection":[{"collection":[{"data":1},{"data":2}]},{"collection":[{"data":3},{"data":4}]}]}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_skipping_nullable_type()
    {
        $toSerialize = new PropertyWithNullUnionType('100');
        $expectedSerializationString = '{"data":"100"}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_throwing_exception_if_converted_type_is_union_type()
    {
        $toSerialize = new PropertyWithUnionType([]);
        $expectedSerializationString = '{"data":[]}';

        $this->expectException(InvalidArgumentException::class);

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString);
    }

    public function test_serializing_with_metadata_cache()
    {
        $toSerialize = new PropertyWithTypeAndMetadataType(5);
        $converter = (new JMSConverterBuilder([], JMSConverterConfiguration::createWithDefaults(), "/tmp/" . Uuid::uuid4()->toString()))->build(InMemoryReferenceSearchService::createWith([]));

        $serialized = $converter->convert($toSerialize, TypeDescriptor::createFromVariable($toSerialize), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationJson());

        $this->assertEquals(
            $toSerialize,
            $converter->convert($serialized, TypeDescriptor::createStringType(), MediaType::createApplicationJson(), TypeDescriptor::createFromVariable($toSerialize), MediaType::createApplicationXPHPObject(), TypeDescriptor::createFromVariable($toSerialize))
        );
    }

    public function test_converting_with_jms_handlers_using_simple_type_to_class_mapping()
    {
        $toSerialize = new Person(new Status("active"));
        $expectedSerializationString = '{"status":"active"}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString, [
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
        ]);
    }

    public function test_converting_with_jms_handlers_using_array_to_class_mapping()
    {
        $toSerialize = new \stdClass();
        $toSerialize->data = "someInformation";
        $expectedSerializationString = '{"data":"someInformation"}';

        $this->assertSerializationAndDeserializationWithJSON($toSerialize, $expectedSerializationString, [
            JMSHandlerAdapter::create(
                TypeDescriptor::createArrayType(),
                TypeDescriptor::create(\stdClass::class),
                ClassToArrayConverter::class,
                "convertFrom"
            ),
            JMSHandlerAdapter::create(
                TypeDescriptor::create(\stdClass::class),
                TypeDescriptor::createArrayType(),
                ClassToArrayConverter::class,
                "convertTo"
            )
        ]);
    }

    public function test_converting_array_of_objects()
    {
        $toSerialize = [new Status("active"), new Status("archived")];
        $expectedSerializationString = '["active","archived"]';

        $jmsHandlerAdapters = [
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
        ];

        $serialized = $this->serialize($toSerialize, $jmsHandlerAdapters);
        $this->assertEquals($expectedSerializationString, $serialized);
        $this->assertEquals($toSerialize, $this->deserialize($serialized, "array<Test\Ecotone\JMSConverter\Fixture\Configuration\Status\Status>", $jmsHandlerAdapters));
    }

    public function test_converting_json_to_array()
    {
        $toSerialize = ["name" => "johny", "surname" => "franco"];
        $expectedSerializationString = '{"name":"johny","surname":"franco"}';

        $serialized = $this->getJMSConverter([])->convert($toSerialize, TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationJson());
        $this->assertEquals($expectedSerializationString, $serialized);
        $this->assertEquals($toSerialize, $this->getJMSConverter([])->convert($serialized, TypeDescriptor::createStringType(), MediaType::createApplicationJson(), TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject()));
    }

    public function test_converting_from_array_to_object()
    {
        $toSerialize = new TwoLevelNestedObjectProperty(new PropertyWithTypeAndMetadataType(3));
        $expectedSerializationObject = ["data" => ["data" => 3]];

        $serialized = $this->getJMSConverter([])->convert($toSerialize, TypeDescriptor::createFromVariable($toSerialize), MediaType::createApplicationXPHPObject(), TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject());
        $this->assertEquals($expectedSerializationObject, $serialized);
        $this->assertEquals($toSerialize, $this->getJMSConverter([])->convert($serialized, TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject(), TypeDescriptor::createFromVariable($toSerialize), MediaType::createApplicationXPHPObject()));
    }

    public function test_converting_to_xml()
    {
        $toSerialize = new Person(new Status("active"));
        $expectedSerializationString = '<?xml version="1.0" encoding="UTF-8"?>
<result>
  <status>
    <type><![CDATA[active]]></type>
  </status>
</result>
';

        $serialized = $this->getJMSConverter([])->convert($toSerialize, TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationXml());
        $this->assertEquals($expectedSerializationString, $serialized);
        $this->assertEquals($toSerialize, $this->getJMSConverter([])->convert($serialized, TypeDescriptor::createStringType(), MediaType::createApplicationXml(), TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject()));
    }

    public function test_matching_conversion_from_array_to_xml()
    {
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationXml())
        );
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createStringType(), MediaType::createApplicationXml(), TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject())
        );
    }

    public function test_matching_conversion_from_object_to_xml()
    {
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationXml())
        );
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createStringType(), MediaType::createApplicationXml(), TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject())
        );
    }

    public function test_matching_conversion_from_array_to_json()
    {
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationJson())
        );
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createStringType(), MediaType::createApplicationJson(), TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject())
        );
    }

    public function test_matching_conversion_from_object_to_json()
    {
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject(), TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject())
        );
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createArrayType(), MediaType::createApplicationXPHPObject(), TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject())
        );
    }

    public function test_matching_conversion_from_array_to_object()
    {
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationJson())
        );
        $this->assertTrue(
            $this->getJMSConverter([])->matches(TypeDescriptor::createStringType(), MediaType::createApplicationJson(), TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject())
        );
    }

    public function test_not_matching_conversion_from_interface()
    {
        $this->assertFalse(
            $this->getJMSConverter([])->matches(TypeDescriptor::create(PersonInterface::class), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationJson())
        );
        $this->assertFalse(
            $this->getJMSConverter([])->matches(TypeDescriptor::createStringType(), MediaType::createApplicationJson(), TypeDescriptor::create(PersonInterface::class), MediaType::createApplicationXPHPObject())
        );
    }

    public function test_not_matching_conversion_from_object_to_format_different_than_xml_and_json()
    {
        $this->assertFalse(
            $this->getJMSConverter([])->matches(TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationOcetStream())
        );
        $this->assertFalse(
            $this->getJMSConverter([])->matches(TypeDescriptor::createStringType(), MediaType::createApplicationOcetStream(), TypeDescriptor::create(Person::class), MediaType::createApplicationXPHPObject())
        );
    }

    private function assertSerializationAndDeserializationWithJSON(object $toSerialize, string $expectedSerializationString, $jmsHandlerAdapters = []): void
    {
        $serialized = $this->serialize($toSerialize, $jmsHandlerAdapters);
        $this->assertEquals($expectedSerializationString, $serialized);
        $this->assertEquals($toSerialize, $this->deserialize($serialized, get_class($toSerialize), $jmsHandlerAdapters));
    }

    private function serialize($data, $jmsHandlerAdapters)
    {
        return $this->getJMSConverter($jmsHandlerAdapters)->convert($data, TypeDescriptor::createFromVariable($data), MediaType::createApplicationXPHPObject(), TypeDescriptor::createStringType(), MediaType::createApplicationJson());
    }

    private function deserialize(string $data, string $type, $jmsHandlerAdapters)
    {
        return $this->getJMSConverter($jmsHandlerAdapters)->convert($data, TypeDescriptor::createStringType(), MediaType::createApplicationJson(), TypeDescriptor::create($type), MediaType::createApplicationXPHPObject(), TypeDescriptor::create($type));
    }

    private function getJMSConverter($jmsHandlerAdapters) : Converter
    {
        return (new JMSConverterBuilder($jmsHandlerAdapters, JMSConverterConfiguration::createWithDefaults(), null))->build(InMemoryReferenceSearchService::createWith([
            StatusConverter::class => new StatusConverter(),
            ClassToArrayConverter::class => new ClassToArrayConverter()
        ]));
    }
}