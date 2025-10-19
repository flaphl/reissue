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
use Flaphl\Element\Reissue\Mapping\Loader\LoaderInterface;

/**
 * Standard class metadata factory using loaders.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ClassMetadataAtelier implements ClassMetadataAtelierInterface
{
    use ClassResolverTrait;

    /**
     * @var array<string, ClassMetadata>
     */
    private array $loadedMetadata = [];

    /**
     * @param LoaderInterface $loader The metadata loader
     */
    public function __construct(
        private LoaderInterface $loader
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor(string|object $value): ClassMetadata
    {
        $className = $this->resolveClassName($value);

        if (!isset($this->loadedMetadata[$className])) {
            $this->loadedMetadata[$className] = $this->loader->loadClassMetadata($className);
        }

        return $this->loadedMetadata[$className];
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor(string|object $value): bool
    {
        $className = $this->resolveClassName($value);
        
        if (isset($this->loadedMetadata[$className])) {
            return true;
        }

        // Try to load metadata
        try {
            $this->getMetadataFor($className);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
