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
 * Decoder that needs context information.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ContextAwareDecoderInterface extends DecoderInterface
{
    /**
     * Returns whether this decoder needs denormalization context.
     *
     * @param string $format The format
     *
     * @return bool
     */
    public function needsDenormalization(string $format): bool;
}
