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
 * Chains multiple encoders together.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ChainEncoder implements EncoderInterface
{
    /**
     * @param array<EncoderInterface> $encoders
     */
    public function __construct(
        private readonly array $encoders = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->supportsEncoding($format)) {
                return $encoder->encode($data, $format, $context);
            }
        }

        throw new LogicException(sprintf('No encoder found for format "%s".', $format));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding(string $format): bool
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->supportsEncoding($format)) {
                return true;
            }
        }

        return false;
    }
}
