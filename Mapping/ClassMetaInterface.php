<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping;

/**
 * Interface for class metadata.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ClassMetaInterface
{
    /**
     * Gets the class name.
     */
    public function getClassName(): string;

    /**
     * Gets all attributes metadata.
     *
     * @return array<string, AttributeMetadataInterface>
     */
    public function getAttributesMetadata(): array;

    /**
     * Gets attribute metadata by name.
     */
    public function getAttributeMetadata(string $name): ?AttributeMetadataInterface;
}
