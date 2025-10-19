<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Attribute;

/**
 * Specifies the maximum depth for nested object serialization.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class MaxDepth
{
    public function __construct(
        public readonly int $depth
    ) {
    }
}
