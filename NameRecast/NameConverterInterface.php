<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\NameRecast;

/**
 * Interface for converting property names during serialization.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface NameConverterInterface
{
    /**
     * Converts a property name to its normalized value.
     *
     * @param string $propertyName The property name to convert
     *
     * @return string The converted name
     */
    public function normalize(string $propertyName): string;

    /**
     * Converts a property name from its normalized value.
     *
     * @param string $propertyName The normalized property name
     *
     * @return string The denormalized name
     */
    public function denormalize(string $propertyName): string;
}
