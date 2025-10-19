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
 * Helper class for JSON encoding.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class JsonEncode
{
    /**
     * Encodes data to JSON with options.
     *
     * @param mixed $data The data to encode
     * @param int $options JSON encoding options
     *
     * @return string The JSON string
     *
     * @throws InvalidArgumentException If encoding fails
     */
    public static function encode(mixed $data, int $options = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE): string
    {
        try {
            return json_encode($data, $options | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('Failed to encode JSON: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Encodes data to pretty-printed JSON.
     *
     * @param mixed $data The data to encode
     *
     * @return string The formatted JSON string
     */
    public static function encodePretty(mixed $data): string
    {
        return self::encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
