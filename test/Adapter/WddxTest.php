<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */

declare(strict_types=1);

namespace LaminasTest\Serializer\Adapter;

use Laminas\Serializer;
use Laminas\Serializer\Exception\ExtensionNotLoadedException;
use Laminas\Serializer\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use stdClass;

use function class_exists;
use function extension_loaded;

/**
 * @group      Laminas_Serializer
 * @covers \Laminas\Serializer\Adapter\Wddx
 */
class WddxTest extends TestCase
{
    /** @var Serializer\Adapter\Wddx */
    private $adapter;

    protected function setUp(): void
    {
        if (! extension_loaded('wddx')) {
            try {
                new Serializer\Adapter\Wddx();
                $this->fail("Laminas\\Serializer\\Adapter\\Wddx needs missing ext/wddx but did't throw exception");
            } catch (ExtensionNotLoadedException $e) {
            }
            $this->markTestSkipped('Laminas\\Serializer\\Adapter\\Wddx needs ext/wddx');
        }
        $this->adapter = new Serializer\Adapter\Wddx();
    }

    protected function tearDown(): void
    {
        $this->adapter = null;
    }

    public function testSerializeString()
    {
        $value    = 'test';
        $expected = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><string>test</string></data></wddxPacket>';

        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testSerializeStringWithComment()
    {
        $value    = 'test';
        $expected = '<wddxPacket version=\'1.0\'><header><comment>a test comment</comment></header>'
                  . '<data><string>test</string></data></wddxPacket>';

        $this->adapter->getOptions()->setComment('a test comment');
        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testSerializeFalse()
    {
        $value    = false;
        $expected = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><boolean value=\'false\'/></data></wddxPacket>';

        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testSerializeTrue()
    {
        $value    = true;
        $expected = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><boolean value=\'true\'/></data></wddxPacket>';

        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testSerializeNull()
    {
        $value    = null;
        $expected = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><null/></data></wddxPacket>';

        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testSerializeNumeric()
    {
        $value    = 100;
        $expected = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><number>100</number></data></wddxPacket>';

        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testSerializeObject()
    {
        $value       = new stdClass();
        $value->test = "test";
        $expected    = '<wddxPacket version=\'1.0\'><header/>'
            . '<data><struct>'
            . '<var name=\'php_class_name\'><string>stdClass</string></var>'
            . '<var name=\'test\'><string>test</string></var>'
            . '</struct></data></wddxPacket>';

        $data = $this->adapter->serialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeString()
    {
        $value    = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><string>test</string></data></wddxPacket>';
        $expected = 'test';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeFalse()
    {
        $value    = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><boolean value=\'false\'/></data></wddxPacket>';
        $expected = false;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeTrue()
    {
        $value    = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><boolean value=\'true\'/></data></wddxPacket>';
        $expected = true;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeNull1()
    {
        $value    = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><null/></data></wddxPacket>';
        $expected = null;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    /**
     * test to unserialize a valid null value by a valid wddx
     * but with some differenzes to the null cenerated by php
     * -> the invalid check have to success for all valid wddx null
     */
    public function testUnserializeNull2()
    {
        $value    = '<wddxPacket version=\'1.0\'><header/>' . "\n"
                  . '<data><null/></data></wddxPacket>';
        $expected = null;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeNumeric()
    {
        $value    = '<wddxPacket version=\'1.0\'><header/>'
                  . '<data><number>100</number></data></wddxPacket>';
        $expected = 100;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeObject()
    {
        $value          = '<wddxPacket version=\'1.0\'><header/>'
            . '<data><struct>'
            . '<var name=\'php_class_name\'><string>stdClass</string></var>'
            . '<var name=\'test\'><string>test</string></var>'
            . '</struct></data></wddxPacket>';
        $expected       = new stdClass();
        $expected->test = 'test';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeInvalidXml()
    {
        if (! class_exists('SimpleXMLElement', false)) {
            $this->markTestSkipped('Skipped by missing ext/simplexml');
        }

        $value = 'not a serialized string';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DOMDocument::loadXML(): Start tag expected');
        $this->adapter->unserialize($value);
    }

    public function testUnserialzeInvalidWddx()
    {
        if (! class_exists('SimpleXMLElement', false)) {
            $this->markTestSkipped('Skipped by missing ext/simplexml');
        }

        $value = '<wddxPacket version=\'1.0\'><header /></wddxPacket>';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid wddx packet');
        $this->adapter->unserialize($value);
    }

    public function testShouldThrowExceptionIfXmlToUnserializeFromContainsADoctype()
    {
        $value = '<!DOCTYPE>'
            . '<wddxPacket version=\'1.0\'><header/>'
            . '<data><string>test</string></data></wddxPacket>';
        $this->expectException(RuntimeException::class);
        $data = $this->adapter->unserialize($value);
    }
}
