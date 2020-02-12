<?php

namespace oat\taoBooklet\test\unit\model;

use oat\taoBooklet\model\BookletClassService;
use oat\generis\test\TestCase;
use core_kernel_classes_Resource as Resource;
use core_kernel_classes_Literal as Literal;
use core_kernel_classes_Property as Property;

class BookletClassServiceTest extends TestCase
{
    const URI          = '#attachmentUri';

    public function testGetAttachment()
    {
        $mock = $this->getBookletServiceMock();

        $this->assertEquals(self::URI, $mock->getAttachment($this->getResourceProphecyWithResourcePropertyValue()->reveal()));

        $this->assertEquals(self::URI, $mock->getAttachment($this->getResourceProphecyWithLiteralPropertyValue()->reveal()));

        $this->assertEquals(self::URI, $mock->getAttachment($this->getResourceProphecyWithStringPropertyValue()->reveal()));

        $this->assertNotEquals(self::URI, $mock->getAttachment($this->getResourceProphecyWithUnexpectedPropertyValue()->reveal()));
        $this->assertEquals("", $mock->getAttachment($this->getResourceProphecyWithUnexpectedPropertyValue()->reveal()));
    }

    private function getBookletServiceMock()
    {
        $mock = $this->getMockBuilder(BookletClassService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty'])
            ->getMock();

        $mock->method('getProperty')->with(BookletClassService::PROPERTY_FILE_CONTENT)->willReturn(new Property(BookletClassService::PROPERTY_FILE_CONTENT));

        return $mock;
    }

    private function createResourceProphecy()
    {
        $resourceProphecy = $this->prophesize('core_kernel_classes_Resource');

        return $resourceProphecy;
    }

    private function getResourceProphecyWithResourcePropertyValue()
    {
        $resourceProphecy = $this->createResourceProphecy();
        $resourceProphecy->getOnePropertyValue(new Property(BookletClassService::PROPERTY_FILE_CONTENT))->willReturn(new Resource(self::URI));

        return $resourceProphecy;
    }

    private function getResourceProphecyWithLiteralPropertyValue()
    {
        $resourceProphecy = $this->createResourceProphecy();
        $resourceProphecy->getOnePropertyValue(new Property(BookletClassService::PROPERTY_FILE_CONTENT))->willReturn(new Literal(self::URI));

        return $resourceProphecy;
    }

    private function getResourceProphecyWithStringPropertyValue()
    {
        $resourceProphecy = $this->createResourceProphecy();
        $resourceProphecy->getOnePropertyValue(new Property(BookletClassService::PROPERTY_FILE_CONTENT))->willReturn(self::URI);

        return $resourceProphecy;
    }

    private function getResourceProphecyWithUnexpectedPropertyValue()
    {
        $resourceProphecy = $this->createResourceProphecy();
        $resourceProphecy->getOnePropertyValue(new Property(BookletClassService::PROPERTY_FILE_CONTENT))->willReturn(false);

        return $resourceProphecy;
    }
}
