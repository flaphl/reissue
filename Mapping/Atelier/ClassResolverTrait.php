<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping\Atelier;

/**
 * Trait for resolving class names from strings or objects.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
trait ClassResolverTrait
{
    /**
     * Resolves a class name from a string or object.
     *
     * @param string|object $value A class name or object instance
     */
    protected function resolveClassName(string|object $value): string
    {
        return is_object($value) ? get_class($value) : $value;
    }
}
