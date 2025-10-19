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
 * Specifies which groups a property belongs to for selective serialization.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class Groups
{
    public readonly array $groups;

    public function __construct(string|array $groups)
    {
        $this->groups = (array) $groups;
    }
}
