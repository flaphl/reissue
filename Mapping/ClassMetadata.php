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
 * Metadata for a class.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ClassMetadata implements ClassMetaInterface
{
    /**
     * @var array<string, AttributeMetadataInterface>
     */
    private array $attributesMetadata = [];

    private array $groups = [];
    private ?int $maxDepth = null;

    public function __construct(
        private readonly string $className
    ) {
    }

    /**
     * Gets the class name.
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Adds attribute metadata.
     */
    public function addAttributeMetadata(AttributeMetadataInterface $metadata): void
    {
        $this->attributesMetadata[$metadata->getName()] = $metadata;
    }

    /**
     * Gets attribute metadata by name.
     */
    public function getAttributeMetadata(string $name): ?AttributeMetadataInterface
    {
        return $this->attributesMetadata[$name] ?? null;
    }

    /**
     * Gets all attributes metadata.
     *
     * @return array<string, AttributeMetadataInterface>
     */
    public function getAttributesMetadata(): array
    {
        return $this->attributesMetadata;
    }

    /**
     * Sets groups for the class.
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * Gets groups for the class.
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Sets max depth for the class.
     */
    public function setMaxDepth(?int $depth): void
    {
        $this->maxDepth = $depth;
    }

    /**
     * Gets max depth for the class.
     */
    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * Checks if an attribute should be serialized based on groups.
     */
    public function shouldSerializeAttribute(string $name, array $contextGroups = []): bool
    {
        if (empty($contextGroups)) {
            return true;
        }

        $metadata = $this->getAttributeMetadata($name);
        if (!$metadata) {
            return true;
        }

        $attributeGroups = $metadata->getGroups();
        if (empty($attributeGroups)) {
            return true;
        }

        return !empty(array_intersect($attributeGroups, $contextGroups));
    }
}
