<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Context\Encoder;

use Flaphl\Element\Reissue\Context\Encoder\JsonEncoderContextBuilder;
use PHPUnit\Framework\TestCase;

class JsonEncoderContextBuilderTest extends TestCase
{
    public function testWithJsonEncodeOptions(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withJsonEncodeOptions(JSON_PRETTY_PRINT)->toArray();

        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals(JSON_PRETTY_PRINT, $context['json_encode_options']);
    }

    public function testWithJsonDecodeOptions(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withJsonDecodeOptions(JSON_BIGINT_AS_STRING)->toArray();

        $this->assertArrayHasKey('json_decode_options', $context);
        $this->assertEquals(JSON_BIGINT_AS_STRING, $context['json_decode_options']);
    }

    public function testWithPrettyPrint(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withPrettyPrint()->toArray();

        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals(JSON_PRETTY_PRINT, $context['json_encode_options'] & JSON_PRETTY_PRINT);
    }

    public function testWithPrettyPrintDisabled(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withPrettyPrint(false)->toArray();

        $this->assertArrayNotHasKey('json_encode_options', $context);
    }

    public function testWithPreserveZeroFraction(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withPreserveZeroFraction()->toArray();

        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals(JSON_PRESERVE_ZERO_FRACTION, $context['json_encode_options'] & JSON_PRESERVE_ZERO_FRACTION);
    }

    public function testWithUnescapedUnicode(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withUnescapedUnicode()->toArray();

        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals(JSON_UNESCAPED_UNICODE, $context['json_encode_options'] & JSON_UNESCAPED_UNICODE);
    }

    public function testWithUnescapedSlashes(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withUnescapedSlashes()->toArray();

        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals(JSON_UNESCAPED_SLASHES, $context['json_encode_options'] & JSON_UNESCAPED_SLASHES);
    }

    public function testMultipleOptionsCanBeCombined(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder
            ->withPrettyPrint()
            ->withPreserveZeroFraction()
            ->withUnescapedUnicode()
            ->withUnescapedSlashes()
            ->toArray();

        $expected = JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        
        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals($expected, $context['json_encode_options']);
    }

    public function testFluentInterface(): void
    {
        $builder = new JsonEncoderContextBuilder();
        
        $result = $builder->withPrettyPrint();
        $this->assertInstanceOf(JsonEncoderContextBuilder::class, $result);
        
        $result = $builder->withPreserveZeroFraction();
        $this->assertInstanceOf(JsonEncoderContextBuilder::class, $result);
        
        $result = $builder->withUnescapedUnicode();
        $this->assertInstanceOf(JsonEncoderContextBuilder::class, $result);
        
        $result = $builder->withUnescapedSlashes();
        $this->assertInstanceOf(JsonEncoderContextBuilder::class, $result);
        
        $result = $builder->withJsonEncodeOptions(0);
        $this->assertInstanceOf(JsonEncoderContextBuilder::class, $result);
        
        $result = $builder->withJsonDecodeOptions(0);
        $this->assertInstanceOf(JsonEncoderContextBuilder::class, $result);
    }

    public function testOptionsAreAccumulative(): void
    {
        $builder = new JsonEncoderContextBuilder();
        
        // Set initial option
        $builder->withJsonEncodeOptions(JSON_PRETTY_PRINT);
        
        // Add another option (should combine, not replace)
        $context = $builder->withPreserveZeroFraction()->toArray();
        
        $this->assertEquals(
            JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION,
            $context['json_encode_options']
        );
    }

    public function testWithJsonEncodeOptionsOverwrites(): void
    {
        $builder = new JsonEncoderContextBuilder();
        
        // Build up some flags
        $builder->withPrettyPrint()->withUnescapedUnicode();
        
        // Explicitly set options (should overwrite)
        $context = $builder->withJsonEncodeOptions(JSON_FORCE_OBJECT)->toArray();
        
        $this->assertEquals(JSON_FORCE_OBJECT, $context['json_encode_options']);
    }

    public function testBothEncodeAndDecodeOptions(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder
            ->withJsonEncodeOptions(JSON_PRETTY_PRINT)
            ->withJsonDecodeOptions(JSON_BIGINT_AS_STRING)
            ->toArray();

        $this->assertEquals(JSON_PRETTY_PRINT, $context['json_encode_options']);
        $this->assertEquals(JSON_BIGINT_AS_STRING, $context['json_decode_options']);
    }

    public function testZeroOptionsAreValid(): void
    {
        $builder = new JsonEncoderContextBuilder();
        $context = $builder->withJsonEncodeOptions(0)->toArray();

        $this->assertArrayHasKey('json_encode_options', $context);
        $this->assertEquals(0, $context['json_encode_options']);
    }
}
