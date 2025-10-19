<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Encoder;

use Flaphl\Element\Reissue\Exception\InvalidArgumentException;

/**
 * Helper class for JSON decoding.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class JsonDecode
{
    /**
     * Decodes JSON data with options.
     *
     * @param string $json The JSON string to decode
     * @param bool $associative Whether to return associative arrays
     * @param int $options JSON decoding options
     *
     * @return mixed The decoded data
     *
     * @throws InvalidArgumentException If decoding fails
     */
    public static function decode(string $json, bool $associative = true, int $options = JSON_BIGINT_AS_STRING): mixed
    {
        try {
            return json_decode($json, $associative, 512, $options | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('Failed to decode JSON: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Decodes JSON to an object.
     *
     * @param string $json The JSON string to decode
     *
     * @return object The decoded object
     */
    public static function decodeToObject(string $json): object
    {
        return self::decode($json, false);
    }

    /**
     * Decodes JSON to an array.
     *
     * @param string $json The JSON string to decode
     *
     * @return array The decoded array
     */
    public static function decodeToArray(string $json): array
    {
        $result = self::decode($json, true);
        return is_array($result) ? $result : [];
    }
}
