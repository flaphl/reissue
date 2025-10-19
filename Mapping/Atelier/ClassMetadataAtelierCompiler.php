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
 * Interface for class metadata factory compilers.
 * Used to generate optimized metadata factories for production.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ClassMetadataAtelierCompiler
{
    /**
     * Compiles metadata for the given classes into an optimized factory.
     *
     * @param string[] $classes The classes to compile metadata for
     * @return string PHP code for the compiled factory
     */
    public function compile(array $classes): string;

    /**
     * Generates a cache key for the compiled factory.
     *
     * @param string[] $classes The classes to compile metadata for
     */
    public function getCacheKey(array $classes): string;
}
