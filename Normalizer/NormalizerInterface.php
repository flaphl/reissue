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
 * Converts objects into a set of arrays/scalars.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface NormalizerInterface
{
    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param mixed $object  The object to normalize
     * @param string|null $format The format being (de-) serialized from or into
     * @param array $context Context options for normalization
     *
     * @return array|string|int|float|bool|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null;

    /**
     * Checks whether the given object can be normalized by this normalizer.
     *
     * @param mixed $data   The data to normalize
     * @param string|null $format The format being serialized to
     * @param array $context Context options
     *
     * @return bool
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool;
}
