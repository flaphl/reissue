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

use Flaphl\Element\Reissue\Mapping\ClassMetadata;

/**
 * Interface for class metadata factories (ateliers).
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ClassMetadataAtelierInterface
{
    /**
     * Gets metadata for the given class.
     *
     * @param string|object $value A class name or object instance
     * @return ClassMetadata The metadata for the class
     */
    public function getMetadataFor(string|object $value): ClassMetadata;

    /**
     * Checks if metadata exists for the given class.
     *
     * @param string|object $value A class name or object instance
     */
    public function hasMetadataFor(string|object $value): bool;
}
