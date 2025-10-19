<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping\Loader;

use Flaphl\Element\Reissue\Mapping\ClassMetadata;

/**
 * Interface for loading class metadata.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface LoaderInterface
{
    /**
     * Loads metadata for the given class.
     *
     * @param string $className The fully qualified class name
     * @return ClassMetadata The metadata for the class
     */
    public function loadClassMetadata(string $className): ClassMetadata;
}
