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

/**
 * Encodes normalized data into a specific format.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface EncoderInterface
{
    /**
     * Encodes data into the given format.
     *
     * @param mixed  $data    The data to encode
     * @param string $format  The format name
     * @param array  $context Options for the encoder
     *
     * @return string The encoded data
     */
    public function encode(mixed $data, string $format, array $context = []): string;

    /**
     * Checks whether the encoder supports the given format.
     *
     * @param string $format The format to check
     *
     * @return bool
     */
    public function supportsEncoding(string $format): bool;
}
