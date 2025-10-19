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
 * Decodes data from a specific format into normalized data.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface DecoderInterface
{
    /**
     * Decodes data from the given format.
     *
     * @param string $data    The data to decode
     * @param string $format  The format name
     * @param array  $context Options for the decoder
     *
     * @return mixed The decoded data
     */
    public function decode(string $data, string $format, array $context = []): mixed;

    /**
     * Checks whether the decoder supports the given format.
     *
     * @param string $format The format to check
     *
     * @return bool
     */
    public function supportsDecoding(string $format): bool;
}
