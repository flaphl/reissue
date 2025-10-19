<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Mapping\Atelier;

use Flaphl\Element\Reissue\Mapping\Atelier\ClassMetadataAtelier;
use Flaphl\Element\Reissue\Mapping\ClassMetadata;
use Flaphl\Element\Reissue\Mapping\AttributeMetadata;
use Flaphl\Element\Reissue\Mapping\Loader\LoaderInterface;
use PHPUnit\Framework\TestCase;

class ClassMetadataAtelierTest extends TestCase
{
    public function testGetMetadataForLoadsMetadata(): void
    {
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once())
            ->method('loadClassMetadata')
            ->with($className)
            ->willReturn($expectedMetadata);

        $atelier = new ClassMetadataAtelier($loader);
        $metadata = $atelier->getMetadataFor($className);

        $this->assertSame($expectedMetadata, $metadata);
    }

    public function testGetMetadataForWithObjectInstance(): void
    {
        $object = new \stdClass();
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once())
            ->method('loadClassMetadata')
            ->with($className)
            ->willReturn($expectedMetadata);

        $atelier = new ClassMetadataAtelier($loader);
        $metadata = $atelier->getMetadataFor($object);

        $this->assertSame($expectedMetadata, $metadata);
    }

    public function testGetMetadataForCachesMetadata(): void
    {
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once()) // Should only be called once
            ->method('loadClassMetadata')
            ->with($className)
            ->willReturn($expectedMetadata);

        $atelier = new ClassMetadataAtelier($loader);
        
        // Call twice
        $metadata1 = $atelier->getMetadataFor($className);
        $metadata2 = $atelier->getMetadataFor($className);

        // Should return the same instance (cached)
        $this->assertSame($metadata1, $metadata2);
    }

    public function testHasMetadataForReturnsTrueWhenCached(): void
    {
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')->willReturn($expectedMetadata);

        $atelier = new ClassMetadataAtelier($loader);
        
        // Load metadata first
        $atelier->getMetadataFor($className);

        // Should return true without calling loader again
        $this->assertTrue($atelier->hasMetadataFor($className));
    }

    public function testHasMetadataForReturnsTrueWhenLoadable(): void
    {
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')->willReturn($expectedMetadata);

        $atelier = new ClassMetadataAtelier($loader);

        // Should return true even if not cached (can be loaded)
        $this->assertTrue($atelier->hasMetadataFor($className));
    }

    public function testHasMetadataForReturnsFalseWhenLoaderThrowsException(): void
    {
        $className = 'NonExistentClass';

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')
            ->willThrowException(new \Exception('Class not found'));

        $atelier = new ClassMetadataAtelier($loader);

        $this->assertFalse($atelier->hasMetadataFor($className));
    }

    public function testHasMetadataForWithObjectInstance(): void
    {
        $object = new \stdClass();
        $expectedMetadata = new ClassMetadata(\stdClass::class);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')->willReturn($expectedMetadata);

        $atelier = new ClassMetadataAtelier($loader);

        $this->assertTrue($atelier->hasMetadataFor($object));
    }

    public function testGetMetadataForDifferentClasses(): void
    {
        $class1 = \stdClass::class;
        $class2 = \ArrayObject::class;

        $metadata1 = new ClassMetadata($class1);
        $metadata2 = new ClassMetadata($class2);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')
            ->willReturnCallback(function ($className) use ($metadata1, $metadata2, $class1, $class2) {
                return $className === $class1 ? $metadata1 : $metadata2;
            });

        $atelier = new ClassMetadataAtelier($loader);

        $result1 = $atelier->getMetadataFor($class1);
        $result2 = $atelier->getMetadataFor($class2);

        $this->assertSame($metadata1, $result1);
        $this->assertSame($metadata2, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function testGetMetadataForPreservesAttributeMetadata(): void
    {
        $className = \stdClass::class;
        $metadata = new ClassMetadata($className);
        
        $attr1 = new AttributeMetadata('property1');
        $attr1->setSerializedName('prop_1');
        $metadata->addAttributeMetadata($attr1);

        $attr2 = new AttributeMetadata('property2');
        $attr2->setGroups(['public']);
        $metadata->addAttributeMetadata($attr2);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('loadClassMetadata')->willReturn($metadata);

        $atelier = new ClassMetadataAtelier($loader);
        $result = $atelier->getMetadataFor($className);

        $attributes = $result->getAttributesMetadata();
        $this->assertCount(2, $attributes);
        $this->assertEquals('prop_1', $attributes['property1']->getSerializedName());
        $this->assertEquals(['public'], $attributes['property2']->getGroups());
    }

    public function testCacheIsPerClass(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->exactly(2))
            ->method('loadClassMetadata')
            ->willReturnCallback(function ($className) {
                return new ClassMetadata($className);
            });

        $atelier = new ClassMetadataAtelier($loader);

        // Load two different classes
        $atelier->getMetadataFor(\stdClass::class);
        $atelier->getMetadataFor(\ArrayObject::class);

        // Load them again - should not call loader (cached)
        $atelier->getMetadataFor(\stdClass::class);
        $atelier->getMetadataFor(\ArrayObject::class);
    }
}
