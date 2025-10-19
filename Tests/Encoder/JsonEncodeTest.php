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

use Flaphl\Element\Reissue\Encoder\JsonEncode;
use Flaphl\Element\Reissue\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class JsonEncodeTest extends TestCase
{
    public function testEncodeBasicArray(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $result = JsonEncode::encode($data);

        $this->assertEquals('{"name":"John","age":30}', $result);
    }

    public function testEncodeWithOptions(): void
    {
        $data = ['url' => 'https://example.com/path'];
        $result = JsonEncode::encode($data, JSON_UNESCAPED_SLASHES);

        $this->assertStringContainsString('https://example.com/path', $result);
    }

    public function testEncodePretty(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $result = JsonEncode::encodePretty($data);

        $this->assertStringContainsString("\n", $result);
        $this->assertStringContainsString('    ', $result);
    }

    public function testEncodeFloat(): void
    {
        $data = ['value' => 1.0];
        $result = JsonEncode::encode($data);

        // With JSON_PRESERVE_ZERO_FRACTION, should be "1.0" not "1"
        $this->assertStringContainsString('1.0', $result);
    }

    public function testEncodeUnicode(): void
    {
        $data = ['text' => 'Hello 世界'];
        $result = JsonEncode::encode($data);

        // With JSON_UNESCAPED_UNICODE, should contain actual unicode characters
        $this->assertStringContainsString('世界', $result);
    }

    public function testEncodeEmptyArray(): void
    {
        $result = JsonEncode::encode([]);
        $this->assertEquals('[]', $result);
    }

    public function testEncodeNull(): void
    {
        $result = JsonEncode::encode(null);
        $this->assertEquals('null', $result);
    }

    public function testEncodeBooleans(): void
    {
        $data = ['true' => true, 'false' => false];
        $result = JsonEncode::encode($data);

        $this->assertStringContainsString('"true":true', $result);
        $this->assertStringContainsString('"false":false', $result);
    }

    public function testEncodeNestedArrays(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'city' => 'New York',
                    'zip' => '10001'
                ]
            ]
        ];
        
        $result = JsonEncode::encode($data);
        $decoded = json_decode($result, true);

        $this->assertEquals($data, $decoded);
    }

    public function testEncodeInvalidUtf8ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Failed to encode.*JSON/i');

        // Invalid UTF-8 sequence
        $invalidUtf8 = "\xB1\x31";
        JsonEncode::encode(['text' => $invalidUtf8]);
    }

    public function testEncodeRecursiveArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $data = [];
        $data['self'] = &$data;
        
        JsonEncode::encode($data);
    }

    public function testEncodeWithDepthLimit(): void
    {
        $deeply_nested = ['level1' => ['level2' => ['level3' => ['level4' => ['level5' => 'deep']]]]];
        
        // This should work with default depth
        $result = JsonEncode::encode($deeply_nested);
        $this->assertIsString($result);
    }

    public function testEncodePrettyFormatsCorrectly(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $result = JsonEncode::encodePretty($data);

        $expected = <<<JSON
{
    "a": 1,
    "b": 2
}
JSON;

        $this->assertEquals($expected, $result);
    }

    public function testEncodeStdClass(): void
    {
        $obj = new \stdClass();
        $obj->name = 'Test';
        $obj->value = 123;

        $result = JsonEncode::encode($obj);
        $this->assertEquals('{"name":"Test","value":123}', $result);
    }

    public function testEncodeNumericArray(): void
    {
        $data = [1, 2, 3, 4, 5];
        $result = JsonEncode::encode($data);

        $this->assertEquals('[1,2,3,4,5]', $result);
    }

    public function testEncodeAssociativeArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $result = JsonEncode::encode($data);

        $this->assertStringContainsString('"key1":"value1"', $result);
        $this->assertStringContainsString('"key2":"value2"', $result);
    }

    public function testEncodeWithCustomOptions(): void
    {
        $data = ['empty' => []];
        $result = JsonEncode::encode($data, JSON_FORCE_OBJECT);

        // With JSON_FORCE_OBJECT, even empty arrays should be objects
        $this->assertStringContainsString('"empty":{}', $result);
    }
}
