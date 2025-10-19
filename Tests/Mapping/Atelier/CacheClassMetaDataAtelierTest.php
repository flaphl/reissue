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

use Flaphl\Element\Reissue\Mapping\Atelier\CacheClassMetaDataAtelier;
use Flaphl\Element\Reissue\Mapping\Atelier\ClassMetadataAtelierInterface;
use Flaphl\Element\Reissue\Mapping\ClassMetadata;
use Flaphl\Element\Reissue\Mapping\AttributeMetadata;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use PHPUnit\Framework\TestCase;

class CacheClassMetaDataAtelierTest extends TestCase
{
    public function testGetMetadataForWhenCached(): void
    {
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn($expectedMetadata);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);
        $cache->expects($this->never())->method('save'); // Should not save if already cached

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->expects($this->never())->method('getMetadataFor'); // Should not call decorated

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        $result = $atelier->getMetadataFor($className);

        $this->assertSame($expectedMetadata, $result);
    }

    public function testGetMetadataForWhenNotCached(): void
    {
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->expects($this->once())->method('set')->with($expectedMetadata);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);
        $cache->expects($this->once())->method('save')->with($cacheItem);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->expects($this->once())
            ->method('getMetadataFor')
            ->with($className)
            ->willReturn($expectedMetadata);

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        $result = $atelier->getMetadataFor($className);

        $this->assertSame($expectedMetadata, $result);
    }

    public function testGetMetadataForWithObjectInstance(): void
    {
        $object = new \stdClass();
        $className = \stdClass::class;
        $expectedMetadata = new ClassMetadata($className);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->method('set')->willReturn($cacheItem);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->method('getMetadataFor')->willReturn($expectedMetadata);

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        $result = $atelier->getMetadataFor($object);

        $this->assertSame($expectedMetadata, $result);
    }

    public function testHasMetadataForWhenCached(): void
    {
        $className = \stdClass::class;

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects($this->once())
            ->method('hasItem')
            ->willReturn(true);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->expects($this->never())->method('hasMetadataFor');

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        
        $this->assertTrue($atelier->hasMetadataFor($className));
    }

    public function testHasMetadataForWhenNotCached(): void
    {
        $className = \stdClass::class;

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('hasItem')->willReturn(false);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->expects($this->once())
            ->method('hasMetadataFor')
            ->with($className)
            ->willReturn(true);

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        
        $this->assertTrue($atelier->hasMetadataFor($className));
    }

    public function testHasMetadataForReturnsFalse(): void
    {
        $className = 'NonExistent';

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('hasItem')->willReturn(false);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->method('hasMetadataFor')->willReturn(false);

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        
        $this->assertFalse($atelier->hasMetadataFor($className));
    }

    public function testCacheKeyGeneration(): void
    {
        $className = 'App\\Entity\\User';
        $expectedCacheKey = 'reissue.metadata.App.Entity.User';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn(new ClassMetadata($className));

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedCacheKey)
            ->willReturn($cacheItem);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        $atelier->getMetadataFor($className);
    }

    public function testMultipleCallsForSameClass(): void
    {
        $className = \stdClass::class;
        $metadata = new ClassMetadata($className);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')
            ->willReturnOnConsecutiveCalls(false, true, true); // First miss, then hits
        $cacheItem->method('get')->willReturn($metadata);
        $cacheItem->method('set')->willReturn($cacheItem);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);
        $cache->expects($this->once())->method('save'); // Only save once

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->expects($this->once()) // Only load once
            ->method('getMetadataFor')
            ->willReturn($metadata);

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        
        // Call three times
        $atelier->getMetadataFor($className);
        $atelier->getMetadataFor($className);
        $atelier->getMetadataFor($className);
    }

    public function testDifferentClassesHaveDifferentCacheKeys(): void
    {
        $class1 = \stdClass::class;
        $class2 = \ArrayObject::class;

        $cacheItem1 = $this->createMock(CacheItemInterface::class);
        $cacheItem1->method('isHit')->willReturn(false);
        $cacheItem1->method('set')->willReturn($cacheItem1);

        $cacheItem2 = $this->createMock(CacheItemInterface::class);
        $cacheItem2->method('isHit')->willReturn(false);
        $cacheItem2->method('set')->willReturn($cacheItem2);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects($this->exactly(2))
            ->method('getItem')
            ->willReturnOnConsecutiveCalls($cacheItem1, $cacheItem2);

        $decorated = $this->createMock(ClassMetadataAtelierInterface::class);
        $decorated->method('getMetadataFor')
            ->willReturnCallback(function ($className) {
                return new ClassMetadata($className);
            });

        $atelier = new CacheClassMetaDataAtelier($decorated, $cache);
        
        $atelier->getMetadataFor($class1);
        $atelier->getMetadataFor($class2);
    }
}
