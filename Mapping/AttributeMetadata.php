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
 * Metadata for a class attribute/property.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class AttributeMetadata implements AttributeMetadataInterface
{
    private ?string $serializedName = null;
    private bool $ignored = false;
    private array $groups = [];
    private ?int $maxDepth = null;

    public function __construct(
        private readonly string $name
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the serialized name.
     */
    public function setSerializedName(?string $name): void
    {
        $this->serializedName = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializedName(): ?string
    {
        return $this->serializedName;
    }

    /**
     * Sets whether the attribute is ignored.
     */
    public function setIgnored(bool $ignored): void
    {
        $this->ignored = $ignored;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnored(): bool
    {
        return $this->ignored;
    }

    /**
     * Sets groups for the attribute.
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Sets max depth for the attribute.
     */
    public function setMaxDepth(?int $depth): void
    {
        $this->maxDepth = $depth;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }
}
