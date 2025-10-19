<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Mapping\Loader;

use Flaphl\Element\Reissue\Mapping\ClassMetadata;
use Flaphl\Element\Reissue\Mapping\Loader\LoaderChain;
use Flaphl\Element\Reissue\Mapping\Loader\LoaderInterface;
use Flaphl\Element\Reissue\Mapping\AttributeMetadata;
use PHPUnit\Framework\TestCase;

class LoaderChainTest extends TestCase
{
    public function testConstructorWithEmptyArray(): void
    {
        $chain = new LoaderChain([]);
        $metadata = $chain->loadClassMetadata(\stdClass::class);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(\stdClass::class, $metadata->getClassName());
    }

    public function testConstructorWithLoaders(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $chain = new LoaderChain([$loader1, $loader2]);
        $this->assertInstanceOf(LoaderChain::class, $chain);
    }

    public function testAddLoader(): void
    {
        $chain = new LoaderChain();
        $loader = $this->createMock(LoaderInterface::class);

        $chain->addLoader($loader);
        $this->assertInstanceOf(LoaderChain::class, $chain);
    }

    public function testLoadClassMetadataFromSingleLoader(): void
    {
        $metadata = new ClassMetadata(\stdClass::class);
        $attrMetadata = new AttributeMetadata('testProperty');
        $metadata->addAttributeMetadata($attrMetadata);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once())
            ->method('loadClassMetadata')
            ->with(\stdClass::class)
            ->willReturn($metadata);

        $chain = new LoaderChain([$loader]);
        $result = $chain->loadClassMetadata(\stdClass::class);

        $this->assertInstanceOf(ClassMetadata::class, $result);
        $this->assertCount(1, $result->getAttributesMetadata());
    }

    public function testLoadClassMetadataMergesFromMultipleLoaders(): void
    {
        $className = \stdClass::class;

        // First loader provides one attribute
        $metadata1 = new ClassMetadata($className);
        $attr1 = new AttributeMetadata('property1');
        $metadata1->addAttributeMetadata($attr1);

        // Second loader provides another attribute
        $metadata2 = new ClassMetadata($className);
        $attr2 = new AttributeMetadata('property2');
        $metadata2->addAttributeMetadata($attr2);

        $loader1 = $this->createMock(LoaderInterface::class);
        $loader1->method('loadClassMetadata')->willReturn($metadata1);

        $loader2 = $this->createMock(LoaderInterface::class);
        $loader2->method('loadClassMetadata')->willReturn($metadata2);

        $chain = new LoaderChain([$loader1, $loader2]);
        $result = $chain->loadClassMetadata($className);

        $attributes = $result->getAttributesMetadata();
        $this->assertCount(2, $attributes);
        $this->assertArrayHasKey('property1', $attributes);
        $this->assertArrayHasKey('property2', $attributes);
    }

    public function testLoadClassMetadataCallsAllLoaders(): void
    {
        $className = \stdClass::class;

        $loader1 = $this->createMock(LoaderInterface::class);
        $loader1->expects($this->once())
            ->method('loadClassMetadata')
            ->with($className)
            ->willReturn(new ClassMetadata($className));

        $loader2 = $this->createMock(LoaderInterface::class);
        $loader2->expects($this->once())
            ->method('loadClassMetadata')
            ->with($className)
            ->willReturn(new ClassMetadata($className));

        $loader3 = $this->createMock(LoaderInterface::class);
        $loader3->expects($this->once())
            ->method('loadClassMetadata')
            ->with($className)
            ->willReturn(new ClassMetadata($className));

        $chain = new LoaderChain([$loader1, $loader2, $loader3]);
        $chain->loadClassMetadata($className);
    }

    public function testAddLoaderAfterConstruction(): void
    {
        $className = \stdClass::class;

        $metadata = new ClassMetadata($className);
        $attr = new AttributeMetadata('newProperty');
        $metadata->addAttributeMetadata($attr);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')->willReturn($metadata);

        $chain = new LoaderChain();
        $chain->addLoader($loader);

        $result = $chain->loadClassMetadata($className);
        $this->assertCount(1, $result->getAttributesMetadata());
    }

    public function testMultipleLoadersCanProvideMetadataForSameProperty(): void
    {
        $className = \stdClass::class;

        // Both loaders provide metadata for the same property
        // The second one should overwrite
        $metadata1 = new ClassMetadata($className);
        $attr1 = new AttributeMetadata('property');
        $attr1->setSerializedName('first_name');
        $metadata1->addAttributeMetadata($attr1);

        $metadata2 = new ClassMetadata($className);
        $attr2 = new AttributeMetadata('property');
        $attr2->setSerializedName('second_name');
        $metadata2->addAttributeMetadata($attr2);

        $loader1 = $this->createMock(LoaderInterface::class);
        $loader1->method('loadClassMetadata')->willReturn($metadata1);

        $loader2 = $this->createMock(LoaderInterface::class);
        $loader2->method('loadClassMetadata')->willReturn($metadata2);

        $chain = new LoaderChain([$loader1, $loader2]);
        $result = $chain->loadClassMetadata($className);

        $attributes = $result->getAttributesMetadata();
        $this->assertCount(1, $attributes);
        
        // Second loader should have overwritten
        $property = $attributes['property'];
        $this->assertEquals('second_name', $property->getSerializedName());
    }

    public function testEmptyChainReturnsEmptyMetadata(): void
    {
        $chain = new LoaderChain([]);
        $result = $chain->loadClassMetadata(\stdClass::class);

        $this->assertInstanceOf(ClassMetadata::class, $result);
        $this->assertCount(0, $result->getAttributesMetadata());
    }

    public function testChainPreservesAttributeOrder(): void
    {
        $className = \stdClass::class;

        $metadata1 = new ClassMetadata($className);
        $metadata1->addAttributeMetadata(new AttributeMetadata('a'));
        $metadata1->addAttributeMetadata(new AttributeMetadata('b'));

        $metadata2 = new ClassMetadata($className);
        $metadata2->addAttributeMetadata(new AttributeMetadata('c'));
        $metadata2->addAttributeMetadata(new AttributeMetadata('d'));

        $loader1 = $this->createMock(LoaderInterface::class);
        $loader1->method('loadClassMetadata')->willReturn($metadata1);

        $loader2 = $this->createMock(LoaderInterface::class);
        $loader2->method('loadClassMetadata')->willReturn($metadata2);

        $chain = new LoaderChain([$loader1, $loader2]);
        $result = $chain->loadClassMetadata($className);

        $attributeNames = array_keys($result->getAttributesMetadata());
        $this->assertEquals(['a', 'b', 'c', 'd'], $attributeNames);
    }
}
