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

use Flaphl\Element\Reissue\Context\Encoder\XmlEncoderContextBuilder;
use PHPUnit\Framework\TestCase;

class XmlEncoderContextBuilderTest extends TestCase
{
    public function testWithRootNodeName(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withRootNodeName('customRoot')->toArray();

        $this->assertArrayHasKey('xml_root_node_name', $context);
        $this->assertEquals('customRoot', $context['xml_root_node_name']);
    }

    public function testWithXmlVersion(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withXmlVersion('1.1')->toArray();

        $this->assertArrayHasKey('xml_version', $context);
        $this->assertEquals('1.1', $context['xml_version']);
    }

    public function testWithXmlEncoding(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withXmlEncoding('ISO-8859-1')->toArray();

        $this->assertArrayHasKey('xml_encoding', $context);
        $this->assertEquals('ISO-8859-1', $context['xml_encoding']);
    }

    public function testWithFormatOutput(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withFormatOutput(true)->toArray();

        $this->assertArrayHasKey('xml_format_output', $context);
        $this->assertTrue($context['xml_format_output']);
    }

    public function testWithFormatOutputDisabled(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withFormatOutput(false)->toArray();

        $this->assertArrayHasKey('xml_format_output', $context);
        $this->assertFalse($context['xml_format_output']);
    }

    public function testWithStandalone(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withStandalone(true)->toArray();

        $this->assertArrayHasKey('xml_standalone', $context);
        $this->assertTrue($context['xml_standalone']);
    }

    public function testWithAttributePrefix(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withAttributePrefix('@')->toArray();

        $this->assertArrayHasKey('xml_attribute_prefix', $context);
        $this->assertEquals('@', $context['xml_attribute_prefix']);
    }

    public function testWithCdata(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withCdata(true)->toArray();

        $this->assertArrayHasKey('xml_use_cdata', $context);
        $this->assertTrue($context['xml_use_cdata']);
    }

    public function testWithCdataDisabled(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->withCdata(false)->toArray();

        $this->assertArrayHasKey('xml_use_cdata', $context);
        $this->assertFalse($context['xml_use_cdata']);
    }

    public function testFluentInterface(): void
    {
        $builder = new XmlEncoderContextBuilder();
        
        $result = $builder->withRootNodeName('root');
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
        
        $result = $builder->withXmlVersion('1.0');
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
        
        $result = $builder->withXmlEncoding('UTF-8');
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
        
        $result = $builder->withFormatOutput();
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
        
        $result = $builder->withStandalone();
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
        
        $result = $builder->withAttributePrefix('@');
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
        
        $result = $builder->withCdata();
        $this->assertInstanceOf(XmlEncoderContextBuilder::class, $result);
    }

    public function testMultipleOptionsCanBeCombined(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder
            ->withRootNodeName('data')
            ->withXmlVersion('1.0')
            ->withXmlEncoding('UTF-8')
            ->withFormatOutput(true)
            ->withStandalone(true)
            ->withAttributePrefix('@')
            ->withCdata(true)
            ->toArray();

        $this->assertEquals('data', $context['xml_root_node_name']);
        $this->assertEquals('1.0', $context['xml_version']);
        $this->assertEquals('UTF-8', $context['xml_encoding']);
        $this->assertTrue($context['xml_format_output']);
        $this->assertTrue($context['xml_standalone']);
        $this->assertEquals('@', $context['xml_attribute_prefix']);
        $this->assertTrue($context['xml_use_cdata']);
    }

    public function testComplexConfiguration(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder
            ->withRootNodeName('response')
            ->withXmlVersion('1.0')
            ->withXmlEncoding('UTF-8')
            ->withFormatOutput(true)
            ->withAttributePrefix('_')
            ->toArray();

        $this->assertCount(5, $context);
        $this->assertEquals('response', $context['xml_root_node_name']);
        $this->assertEquals('1.0', $context['xml_version']);
        $this->assertEquals('UTF-8', $context['xml_encoding']);
        $this->assertTrue($context['xml_format_output']);
        $this->assertEquals('_', $context['xml_attribute_prefix']);
    }

    public function testEmptyStringValues(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder
            ->withRootNodeName('')
            ->withAttributePrefix('')
            ->toArray();

        $this->assertEquals('', $context['xml_root_node_name']);
        $this->assertEquals('', $context['xml_attribute_prefix']);
    }

    public function testDefaultValuesNotSet(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder->toArray();

        $this->assertEmpty($context);
    }

    public function testOverwritingValues(): void
    {
        $builder = new XmlEncoderContextBuilder();
        $context = $builder
            ->withRootNodeName('first')
            ->withRootNodeName('second')
            ->toArray();

        $this->assertEquals('second', $context['xml_root_node_name']);
    }
}
