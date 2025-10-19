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

use Flaphl\Element\Reissue\Exception\LogicException;

/**
 * Chains multiple decoders together.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ChainDecoder implements DecoderInterface
{
    /**
     * @param array<DecoderInterface> $decoders
     */
    public function __construct(
        private readonly array $decoders = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supportsDecoding($format)) {
                return $decoder->decode($data, $format, $context);
            }
        }

        throw new LogicException(sprintf('No decoder found for format "%s".', $format));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding(string $format): bool
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supportsDecoding($format)) {
                return true;
            }
        }

        return false;
    }
}
