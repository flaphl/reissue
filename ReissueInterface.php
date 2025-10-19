<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue;

/**
 * Main serializer interface for converting objects to various formats and back.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ReissueInterface
{
    /**
     * Serializes data into the specified format.
     *
     * @param mixed  $data    The data to serialize
     * @param string $format  The format (json, xml, etc.)
     * @param array  $context Additional context for serialization
     *
     * @return string The serialized data
     */
    public function reissue(mixed $data, string $format, array $context = []): string;

    /**
     * Deserializes data from the specified format into an object.
     *
     * @param string $data    The serialized data
     * @param string $type    The target type/class name
     * @param string $format  The format (json, xml, etc.)
     * @param array  $context Additional context for deserialization
     *
     * @return mixed The deserialized object
     */
    public function deissue(string $data, string $type, string $format, array $context = []): mixed;

    /**
     * Checks whether this serializer can serialize the given data to the specified format.
     *
     * @param mixed  $data   The data to serialize
     * @param string $format The target format
     *
     * @return bool
     */
    public function supportsReissue(mixed $data, string $format): bool;

    /**
     * Checks whether this serializer can deserialize the given data from the specified format.
     *
     * @param string $data   The data to deserialize
     * @param string $type   The target type
     * @param string $format The source format
     *
     * @return bool
     */
    public function supportsDeissue(string $data, string $type, string $format): bool;
}
