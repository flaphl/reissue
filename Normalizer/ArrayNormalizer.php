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
 * Normalizes arrays and denormalizes arrays.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ArrayNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null
    {
        if (!is_array($object)) {
            return $object;
        }

        $normalized = [];
        foreach ($object as $key => $value) {
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return is_array($data) ? $data : [$data];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return is_array($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === 'array';
    }
}
