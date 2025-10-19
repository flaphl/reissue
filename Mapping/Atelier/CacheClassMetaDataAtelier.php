<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping\Atelier;

use Flaphl\Element\Reissue\Mapping\ClassMetadata;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Cached class metadata factory using PSR-6 cache.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class CacheClassMetaDataAtelier implements ClassMetadataAtelierInterface
{
    use ClassResolverTrait;

    private const CACHE_KEY_PREFIX = 'reissue.metadata.';

    /**
     * @param ClassMetadataAtelierInterface $decorated The decorated factory
     * @param CacheItemPoolInterface $cache The PSR-6 cache pool
     */
    public function __construct(
        private ClassMetadataAtelierInterface $decorated,
        private CacheItemPoolInterface $cache
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor(string|object $value): ClassMetadata
    {
        $className = $this->resolveClassName($value);
        $cacheKey = $this->getCacheKey($className);

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $metadata = $this->decorated->getMetadataFor($className);

        $cacheItem->set($metadata);
        $this->cache->save($cacheItem);

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor(string|object $value): bool
    {
        $className = $this->resolveClassName($value);
        $cacheKey = $this->getCacheKey($className);

        if ($this->cache->hasItem($cacheKey)) {
            return true;
        }

        return $this->decorated->hasMetadataFor($className);
    }

    /**
     * Generates a cache key for the given class name.
     */
    private function getCacheKey(string $className): string
    {
        return self::CACHE_KEY_PREFIX . str_replace('\\', '.', $className);
    }
}
