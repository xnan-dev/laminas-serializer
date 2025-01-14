<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */

declare(strict_types=1);

namespace LaminasTest\Serializer\Adapter;

use Laminas\Serializer;
use Laminas\Serializer\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

use function str_pad;

/**
 * @group      Laminas_Serializer
 * @covers \Laminas\Serializer\Adapter\PythonPickle
 */
class PythonPickleUnserializeTest extends TestCase
{
    /** @var Serializer\Adapter\PythonPickle */
    private $adapter;

    protected function setUp(): void
    {
        $this->adapter = new Serializer\Adapter\PythonPickle();
    }

    protected function tearDown(): void
    {
        $this->adapter = null;
    }

    public function testUnserializeNone(): void
    {
        $value    = "N.";
        $expected = null;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeNewTrue(): void
    {
        $value    = "\x80\x02\x88.";
        $expected = true;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeNewFalse(): void
    {
        $value    = "\x80\x02\x89.";
        $expected = false;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeIntTrue(): void
    {
        $value    = "I01\r\n.";
        $expected = true;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeIntFalse(): void
    {
        $value    = "I00\r\n.";
        $expected = false;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeInt(): void
    {
        $value    = "I1\r\n.";
        $expected = 1;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeBinInt(): void
    {
        $value    = "\x80\x02J\xc7\xcf\xff\xff.";
        $expected = -12345;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeBinInt1(): void
    {
        $value    = "\x80\x02K\x02.";
        $expected = 2;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeBinInt2(): void
    {
        $value    = "\x80\x02M\x00\x01.";
        $expected = 256;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeLong(): void
    {
        $value    = "L9876543210L\r\n.";
        $expected = '9876543210';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeLong1(): void
    {
        $value    = "\x80\x02\x8a\x05\xea\x16\xb0\x4c\x02.";
        $expected = '9876543210';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeLong4Positive(): void
    {
        $value    = "\x80\x02\x8b\x07\x00\x00\x00"
                  . str_pad("\xff", 7, "\x7f")
                  . ".";
        $expected = '35887507618889727';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeLong4Negative(): void
    {
        $value    = "\x80\x02\x8b\x07\x00\x00\x00"
                  . str_pad("\x00", 7, "\x9f")
                  . ".";
        $expected = '-27127564814278912';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    /**
     * @requires extension gmp
     */
    public function testUnserializeLong4BigInt(): void
    {
        $value    = "\x80\x02\x8b\x08\x00\x00\x00"
                  . str_pad("\x00", 8, "\x9f")
                  . ".";
        $expected = '-6944656592455360768';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeFloat(): void
    {
        $value    = "F-12345.6789\r\n.";
        $expected = -12345.6789;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeBinFloat(): void
    {
        $value    = "\x80\x02G\xc0\xc8\x1c\xd6\xe6\x31\xf8\xa1.";
        $expected = -12345.6789;

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeString(): void
    {
        $value    = "S'test'\r\np0\r\n.";
        $expected = 'test';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeStringDoubleQuotes(): void
    {
        $value    = "S\"'t'e's't'\"\r\np0\r\n.";
        $expected = "'t'e's't'";

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeStringWithSpecialChars(): void
    {
        $value    = "S'\\x00\\x01\\x02\\x03\\x04\\x05\\x06\\x07\\x08\\t\\n\\x0b\\x0c\\r\\x0e\\x0f"
                  . "\\x10\\x11\\x12\\x13\\x14\\x15\\x16\\x17\\x18\\x19\\x1a\\x1b\\x1c\\x1d\\x1e\\x1f"
                  . "\\xff\\\\\"\\''\r\n"
                  . "p0\r\n.";
        $expected = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f"
                  . "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f"
                  . "\xff\\\"'";

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeBinString(): void
    {
        $value    = "\x80\x02T\x00\x01\x00\x00"
                  . "01234567890123456789012345678901234567890123456789"
                  . "01234567890123456789012345678901234567890123456789"
                  . "01234567890123456789012345678901234567890123456789"
                  . "01234567890123456789012345678901234567890123456789"
                  . "01234567890123456789012345678901234567890123456789012345"
                  . "q\x00.";
        $expected = '01234567890123456789012345678901234567890123456789'
                  . '01234567890123456789012345678901234567890123456789'
                  . '01234567890123456789012345678901234567890123456789'
                  . '01234567890123456789012345678901234567890123456789'
                  . '01234567890123456789012345678901234567890123456789012345';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeShortBinString(): void
    {
        $value    = "\x80\x02U\x04"
                  . "test"
                  . "q\x00.";
        $expected = 'test';

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeUnicode(): void
    {
        $value    = "Vtest\\u0400\r\n" // test + ` + E
                  . "p0\r\n"
                  . ".";
        $expected = "test\xd0\x80";

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeBinUnicode(): void
    {
        $value    = "\x80\x02X\x07\x00\x00\x00test\xd0\x80\n.";
        $expected = "test\xd0\x80\n"; // test + ` + E + \n

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeListAppend(): void
    {
        $value    = "(lp0\r\n"
            . "I1\r\n"
            . "aI2\r\n"
            . "aI3\r\n"
            . "a.";
        $expected = [1, 2, 3];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeEmptyListAppends(): void
    {
        $value    = "\x80\x02]q\x00(K\x01K\x02K\x03e.";
        $expected = [1, 2, 3];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeDictSetItem(): void
    {
        $value    = "(dp0\r\n"
            . "S'test1'\r\n"
            . "p1\r\n"
            . "I1\r\n"
            . "sI0\r\n"
            . "I2\r\n"
            . "sS'test3'\r\n"
            . "p2\r\n"
            . "g2\r\n"
            . "s.";
        $expected = ['test1' => 1, 0 => 2, 'test3' => 'test3'];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeEmptyDictSetItems(): void
    {
        $value    = "\x80\x02}q\x00(U\x05test1q\x01K\x01K\x00K\x02U\x05test3q\x02h\x02u.";
        $expected = ['test1' => 1, 0 => 2, 'test3' => 'test3'];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeTuple(): void
    {
        $value    = "(I1\r\n"
            . "I2\r\n"
            . "I3\r\n"
            . "tp0\r\n"
            . ".";
        $expected = [1, 2, 3];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeTuple1(): void
    {
        $value    = "\x80\x02K\x01\x85q\x00.";
        $expected = [1];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeTuple2(): void
    {
        $value    = "\x80\x02K\x01K\x02\x86q\x00.";
        $expected = [1, 2];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserializeTuple3(): void
    {
        $value    = "\x80\x02K\x01K\x02K\x03\x87q\x00.";
        $expected = [1, 2, 3];

        $data = $this->adapter->unserialize($value);
        $this->assertEquals($expected, $data);
    }

    public function testUnserialzeInvalid(): void
    {
        $value = 'not a serialized string';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid or unknown opcode 'n'");
        $this->adapter->unserialize($value);
    }
}
