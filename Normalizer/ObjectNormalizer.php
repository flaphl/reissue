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
 * Normalizes objects using reflection to access properties.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Data must be an object.');
        }

        $data = [];
        $reflection = new \ReflectionClass($object);

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            // Skip uninitialized properties
            if (!$property->isInitialized($object)) {
                continue;
            }

            $data[$property->getName()] = $this->normalizeValue($value, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array to denormalize to an object.');
        }

        if (!class_exists($type)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $type));
        }

        $reflection = new \ReflectionClass($type);

        // Try to instantiate without constructor
        $object = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $propertyName => $value) {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);

                $propertyType = $property->getType();
                if ($propertyType instanceof \ReflectionNamedType) {
                    $value = $this->denormalizeValue($value, $propertyType->getName(), $format, $context);
                }

                $property->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return is_object($data) && !$data instanceof \DateTimeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return class_exists($type) && is_array($data);
    }

    /**
     * Normalizes a value recursively.
     */
    private function normalizeValue(mixed $value, ?string $format, array $context): mixed
    {
        if (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DateTimeInterface::RFC3339);
            }
            return $this->normalize($value, $format, $context);
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item, $format, $context);
            }
            return $normalized;
        }

        return $value;
    }

    /**
     * Denormalizes a value based on its type.
     */
    private function denormalizeValue(mixed $value, string $type, ?string $format, array $context): mixed
    {
        // Handle built-in types
        if (in_array($type, ['int', 'float', 'string', 'bool', 'array'])) {
            return $value;
        }

        // Handle DateTime
        if (is_a($type, \DateTimeInterface::class, true) && is_string($value)) {
            return new \DateTime($value);
        }

        // Handle objects
        if (class_exists($type) && is_array($value)) {
            return $this->denormalize($value, $type, $format, $context);
        }

        return $value;
    }
}
