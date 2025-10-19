<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping\Loader;

use Flaphl\Element\Reissue\Mapping\ClassMetadata;

/**
 * Chains multiple metadata loaders together.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class LoaderChain implements LoaderInterface
{
    /**
     * @var LoaderInterface[]
     */
    private array $loaders;

    /**
     * @param LoaderInterface[] $loaders The loaders to chain
     */
    public function __construct(array $loaders = [])
    {
        $this->loaders = $loaders;
    }

    /**
     * Adds a loader to the chain.
     */
    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(string $className): ClassMetadata
    {
        $metadata = new ClassMetadata($className);

        foreach ($this->loaders as $loader) {
            $loaderMetadata = $loader->loadClassMetadata($className);
            
            // Merge attribute metadata
            foreach ($loaderMetadata->getAttributesMetadata() as $attributeName => $attributeMetadata) {
                $metadata->addAttributeMetadata($attributeMetadata);
            }
        }

        return $metadata;
    }
}
