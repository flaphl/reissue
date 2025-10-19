<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Normalizer;

/**
 * Converts arrays/scalars into objects.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface DenormalizerInterface
{
    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data    The data to denormalize
     * @param string $type   The target type/class
     * @param string|null $format The format being deserialized from
     * @param array $context Context options for denormalization
     *
     * @return mixed
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed;

    /**
     * Checks whether the given data can be denormalized to the given type.
     *
     * @param mixed $data   The data to denormalize
     * @param string $type  The target type/class
     * @param string|null $format The format being deserialized from
     * @param array $context Context options
     *
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool;
}
