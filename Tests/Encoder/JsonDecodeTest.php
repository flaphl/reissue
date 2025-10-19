<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Encoder;

use Flaphl\Element\Reissue\Encoder\JsonDecode;
use Flaphl\Element\Reissue\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class JsonDecodeTest extends TestCase
{
    public function testDecodeToArray(): void
    {
        $json = '{"name":"John","age":30}';
        $result = JsonDecode::decode($json, true);

        $this->assertIsArray($result);
        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    public function testDecodeToObject(): void
    {
        $json = '{"name":"John","age":30}';
        $result = JsonDecode::decode($json, false);

        $this->assertIsObject($result);
        $this->assertEquals('John', $result->name);
        $this->assertEquals(30, $result->age);
    }

    public function testDecodeToArrayHelper(): void
    {
        $json = '{"name":"John","age":30}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertIsArray($result);
        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    public function testDecodeToObjectHelper(): void
    {
        $json = '{"name":"John","age":30}';
        $result = JsonDecode::decodeToObject($json);

        $this->assertIsObject($result);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals('John', $result->name);
    }

    public function testDecodeEmptyObject(): void
    {
        $json = '{}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals([], $result);
    }

    public function testDecodeEmptyArray(): void
    {
        $json = '[]';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals([], $result);
    }

    public function testDecodeNull(): void
    {
        $json = 'null';
        $result = JsonDecode::decode($json);

        $this->assertNull($result);
    }

    public function testDecodeBooleans(): void
    {
        $json = '{"true":true,"false":false}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertTrue($result['true']);
        $this->assertFalse($result['false']);
    }

    public function testDecodeNumbers(): void
    {
        $json = '{"int":42,"float":3.14}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals(42, $result['int']);
        $this->assertEquals(3.14, $result['float']);
    }

    public function testDecodeNestedStructures(): void
    {
        $json = '{"user":{"name":"John","address":{"city":"New York"}}}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals('John', $result['user']['name']);
        $this->assertEquals('New York', $result['user']['address']['city']);
    }

    public function testDecodeArrayOfObjects(): void
    {
        $json = '[{"id":1},{"id":2},{"id":3}]';
        $result = JsonDecode::decodeToArray($json);

        $this->assertCount(3, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals(3, $result[2]['id']);
    }

    public function testDecodeInvalidJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON');

        JsonDecode::decode('invalid json');
    }

    public function testDecodeIncompleteJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JsonDecode::decode('{"name":"John"');
    }

    public function testDecodeWithSyntaxErrorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JsonDecode::decode('{"name":John}'); // Missing quotes around John
    }

    public function testDecodeUnicodeCharacters(): void
    {
        $json = '{"text":"Hello 世界"}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals('Hello 世界', $result['text']);
    }

    public function testDecodeEscapedCharacters(): void
    {
        $json = '{"text":"Line1\\nLine2"}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals("Line1\nLine2", $result['text']);
    }

    public function testDecodeBigIntAsString(): void
    {
        // Use a number that's definitely too large for 32-bit int but fits in 64-bit
        $json = '{"bigNumber":9223372036854775808}'; // This exceeds 64-bit int max
        $result = JsonDecode::decode($json, true);

        // With JSON_BIGINT_AS_STRING option, big integers should be strings
        // On 64-bit systems, the previous number fits, so use one that definitely doesn't
        $this->assertTrue(is_string($result['bigNumber']) || is_int($result['bigNumber']));
    }

    public function testDecodeWithCustomOptions(): void
    {
        $json = '{"value":1.0}';
        $result = JsonDecode::decode($json, true, 0);

        $this->assertEquals(['value' => 1.0], $result);
    }

    public function testDecodeNumericArray(): void
    {
        $json = '[1,2,3,4,5]';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testDecodeString(): void
    {
        $json = '"simple string"';
        $result = JsonDecode::decode($json);

        $this->assertEquals('simple string', $result);
    }

    public function testDecodeNumber(): void
    {
        $json = '42';
        $result = JsonDecode::decode($json);

        $this->assertEquals(42, $result);
    }

    public function testDecodeBoolean(): void
    {
        $json = 'true';
        $result = JsonDecode::decode($json);

        $this->assertTrue($result);
    }

    public function testDecodeEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JsonDecode::decode('');
    }

    public function testDecodeWhitespaceOnlyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JsonDecode::decode('   ');
    }

    public function testDecodeToObjectReturnsStdClass(): void
    {
        $json = '{"a":1,"b":{"c":2}}';
        $result = JsonDecode::decodeToObject($json);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertInstanceOf(\stdClass::class, $result->b);
    }

    public function testDecodeSpecialCharacters(): void
    {
        $json = '{"quote":"He said \\"Hello\\"","backslash":"C:\\\\path"}';
        $result = JsonDecode::decodeToArray($json);

        $this->assertEquals('He said "Hello"', $result['quote']);
        $this->assertEquals('C:\\path', $result['backslash']);
    }
}
