<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Context;

/**
 * Interface for building serialization/deserialization contexts.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ContextBuilderInterface
{
    /**
     * Sets groups to use for serialization.
     *
     * @param array $groups Array of group names
     *
     * @return static
     */
    public function withGroups(array $groups): static;

    /**
     * Sets the maximum depth for nested objects.
     *
     * @param int $depth Maximum depth
     *
     * @return static
     */
    public function withMaxDepth(int $depth): static;

    /**
     * Sets whether to serialize null values.
     *
     * @param bool $serialize Whether to include null values
     *
     * @return static
     */
    public function withSerializeNull(bool $serialize): static;

    /**
     * Adds a custom context value.
     *
     * @param string $key   Context key
     * @param mixed  $value Context value
     *
     * @return static
     */
    public function with(string $key, mixed $value): static;

    /**
     * Builds and returns the context array.
     *
     * @return array
     */
    public function toArray(): array;
}
