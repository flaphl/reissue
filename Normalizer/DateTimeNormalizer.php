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

use Flaphl\Element\Reissue\Exception\InvalidArgumentException;

/**
 * Normalizes DateTime objects to strings and denormalizes strings to DateTime.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const FORMAT_KEY = 'datetime_format';
    private const DEFAULT_FORMAT = \DateTimeInterface::RFC3339;

    public function __construct(
        private readonly string $format = self::DEFAULT_FORMAT,
        private readonly \DateTimeZone $timezone = new \DateTimeZone('UTC')
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null
    {
        if (!$object instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement DateTimeInterface.');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->format;

        return $object->format($dateTimeFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Data must be a string to denormalize to DateTime.');
        }

        try {
            if ($type === \DateTimeImmutable::class) {
                return new \DateTimeImmutable($data, $this->timezone);
            }

            return new \DateTime($data, $this->timezone);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(sprintf('Failed to parse date "%s": %s', $data, $e->getMessage()), previous: $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \DateTimeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, \DateTimeInterface::class, true);
    }
}
