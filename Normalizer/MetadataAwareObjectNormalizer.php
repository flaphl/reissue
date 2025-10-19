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
use Flaphl\Element\Reissue\Mapping\Loader\AttributeLoader;
use Flaphl\Element\Reissue\NameRecast\NameConverterInterface;

/**
 * Normalizes objects with metadata and name converter support.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class MetadataAwareObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private AttributeLoader $metadataLoader;
    private array $classMetadataCache = [];

    public function __construct(
        private readonly ?NameConverterInterface $nameConverter = null
    ) {
        $this->metadataLoader = new AttributeLoader();
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Data must be an object.');
        }

        $className = get_class($object);
        $metadata = $this->getClassMetadata($className);
        
        $data = [];
        $reflection = new \ReflectionClass($object);
        $contextGroups = $context['groups'] ?? [];
        $currentDepth = $context['current_depth'] ?? 0;
        $maxDepth = $context['max_depth'] ?? null;

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            
            // Skip uninitialized properties
            if (!$property->isInitialized($object)) {
                continue;
            }

            $propertyName = $property->getName();
            $attributeMetadata = $metadata->getAttributeMetadata($propertyName);

            // Check if ignored
            if ($attributeMetadata && $attributeMetadata->isIgnored()) {
                continue;
            }

            // Check groups
            if (!empty($contextGroups) && !$metadata->shouldSerializeAttribute($propertyName, $contextGroups)) {
                continue;
            }

            $value = $property->getValue($object);

            // Skip null values if configured
            if ($value === null && ($context['skip_null_values'] ?? false)) {
                continue;
            }

            // Check max depth
            if ($maxDepth !== null && $currentDepth >= $maxDepth) {
                continue;
            }

            // Get serialized name
            $serializedName = $attributeMetadata?->getSerializedName() ?? $propertyName;
            
            // Apply name converter
            if ($this->nameConverter) {
                $serializedName = $this->nameConverter->normalize($serializedName);
            }

            // Normalize value recursively
            $normalizedValue = $this->normalizeValue($value, $format, array_merge($context, [
                'current_depth' => $currentDepth + 1
            ]));

            $data[$serializedName] = $normalizedValue;
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
        $object = $reflection->newInstanceWithoutConstructor();
        $metadata = $this->getClassMetadata($type);

        foreach ($data as $key => $value) {
            // Apply name converter
            $propertyName = $key;
            if ($this->nameConverter) {
                $propertyName = $this->nameConverter->denormalize($key);
            }

            // Check if metadata has different serialized name
            foreach ($metadata->getAttributesMetadata() as $attrMetadata) {
                if ($attrMetadata->getSerializedName() === $key) {
                    $propertyName = $attrMetadata->getName();
                    break;
                }
            }

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
     * Gets class metadata (with caching).
     */
    private function getClassMetadata(string $className): \Flaphl\Element\Reissue\Mapping\ClassMetadata
    {
        if (!isset($this->classMetadataCache[$className])) {
            $this->classMetadataCache[$className] = $this->metadataLoader->loadClassMetadata($className);
        }

        return $this->classMetadataCache[$className];
    }

    /**
     * Normalizes a value recursively.
     */
    private function normalizeValue(mixed $value, ?string $format, array $context): mixed
    {
        if (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                $dateFormat = $context['datetime_format'] ?? \DateTimeInterface::RFC3339;
                return $value->format($dateFormat);
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
