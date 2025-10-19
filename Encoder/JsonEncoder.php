<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Encoder;

use Flaphl\Element\Reissue\Exception\InvalidArgumentException;

/**
 * Encodes and decodes JSON data.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class JsonEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'json';

    public function __construct(
        private readonly int $encodeOptions = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE,
        private readonly int $decodeOptions = JSON_BIGINT_AS_STRING
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        $options = $context['json_encode_options'] ?? $this->encodeOptions;

        try {
            $encoded = json_encode($data, $options | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('Failed to encode JSON: ' . $e->getMessage(), previous: $e);
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        $associative = $context['json_decode_associative'] ?? true;
        $options = $context['json_decode_options'] ?? $this->decodeOptions;

        try {
            return json_decode($data, $associative, 512, $options | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('Failed to decode JSON: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
