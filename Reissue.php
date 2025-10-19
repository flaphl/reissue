<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue;

use Flaphl\Element\Reissue\Encoder\DecoderInterface;
use Flaphl\Element\Reissue\Encoder\EncoderInterface;
use Flaphl\Element\Reissue\Exception\InvalidArgumentException;
use Flaphl\Element\Reissue\Exception\LogicException;
use Flaphl\Element\Reissue\Normalizer\DenormalizerInterface;
use Flaphl\Element\Reissue\Normalizer\NormalizerInterface;

/**
 * Main serializer class that coordinates normalizers and encoders.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class Reissue implements ReissueInterface
{
    /**
     * @param array<NormalizerInterface> $normalizers
     * @param array<EncoderInterface> $encoders
     */
    public function __construct(
        private readonly array $normalizers = [],
        private readonly array $encoders = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function reissue(mixed $data, string $format, array $context = []): string
    {
        if (!$this->supportsReissue($data, $format)) {
            throw new LogicException(sprintf('Reissue does not support format "%s" for data of type "%s".', $format, get_debug_type($data)));
        }

        // Call beforeReissue if object implements ReissueAwareInterface
        if ($data instanceof ReissueAwareInterface) {
            $data->beforeReissue($context);
        }

        // Normalize the data
        $normalized = $this->normalize($data, $format, $context);

        // Encode the normalized data
        return $this->encode($normalized, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deissue(string $data, string $type, string $format, array $context = []): mixed
    {
        if (!$this->supportsDeissue($data, $type, $format)) {
            throw new LogicException(sprintf('Reissue does not support format "%s" for type "%s".', $format, $type));
        }

        // Decode the data
        $decoded = $this->decode($data, $format, $context);

        // Denormalize the decoded data
        $object = $this->denormalize($decoded, $type, $format, $context);

        // Call afterDeissue if object implements ReissueAwareInterface
        if ($object instanceof ReissueAwareInterface) {
            $object->afterDeissue($context);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsReissue(mixed $data, string $format): bool
    {
        return $this->supportsEncoding($format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDeissue(string $data, string $type, string $format): bool
    {
        return $this->supportsDecoding($format);
    }

    /**
     * Normalizes data.
     */
    private function normalize(mixed $data, string $format, array $context): mixed
    {
        // Handle primitives
        if (is_scalar($data) || $data === null) {
            return $data;
        }

        // Find a normalizer
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsNormalization($data, $format, $context)) {
                return $normalizer->normalize($data, $format, $context);
            }
        }

        // If no normalizer found, try to convert to array
        if (is_object($data)) {
            return get_object_vars($data);
        }

        return $data;
    }

    /**
     * Denormalizes data.
     */
    private function denormalize(mixed $data, string $type, string $format, array $context): mixed
    {
        // Find a denormalizer
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof DenormalizerInterface && $normalizer->supportsDenormalization($data, $type, $format, $context)) {
                return $normalizer->denormalize($data, $type, $format, $context);
            }
        }

        throw new LogicException(sprintf('No denormalizer found for type "%s".', $type));
    }

    /**
     * Encodes normalized data.
     */
    private function encode(mixed $data, string $format, array $context): string
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->supportsEncoding($format)) {
                return $encoder->encode($data, $format, $context);
            }
        }

        throw new LogicException(sprintf('No encoder found for format "%s".', $format));
    }

    /**
     * Decodes data.
     */
    private function decode(string $data, string $format, array $context): mixed
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder instanceof DecoderInterface && $encoder->supportsDecoding($format)) {
                return $encoder->decode($data, $format, $context);
            }
        }

        throw new LogicException(sprintf('No decoder found for format "%s".', $format));
    }

    /**
     * Checks if encoding is supported.
     */
    private function supportsEncoding(string $format): bool
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->supportsEncoding($format)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if decoding is supported.
     */
    private function supportsDecoding(string $format): bool
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder instanceof DecoderInterface && $encoder->supportsDecoding($format)) {
                return true;
            }
        }

        return false;
    }
}
