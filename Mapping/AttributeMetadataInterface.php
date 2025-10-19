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
 * Interface for attribute metadata.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface AttributeMetadataInterface
{
    /**
     * Gets the attribute name.
     */
    public function getName(): string;

    /**
     * Gets the serialized name (or null to use property name).
     */
    public function getSerializedName(): ?string;

    /**
     * Checks if the attribute is ignored.
     */
    public function isIgnored(): bool;

    /**
     * Gets the groups this attribute belongs to.
     */
    public function getGroups(): array;

    /**
     * Gets the max depth for this attribute.
     */
    public function getMaxDepth(): ?int;
}
