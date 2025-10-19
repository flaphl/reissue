<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Context\Encoder;

use Flaphl\Element\Reissue\Context\ContextBuilderInterface;
use Flaphl\Element\Reissue\Context\ContextBuilderTrait;

/**
 * Context builder for JSON encoder specific options.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class JsonEncoderContextBuilder implements ContextBuilderInterface
{
    use ContextBuilderTrait;

    /**
     * Configures JSON encode options.
     *
     * @param int $options JSON encode options (e.g., JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE)
     */
    public function withJsonEncodeOptions(int $options): static
    {
        return $this->with('json_encode_options', $options);
    }

    /**
     * Configures JSON decode options.
     *
     * @param int $options JSON decode options
     */
    public function withJsonDecodeOptions(int $options): static
    {
        return $this->with('json_decode_options', $options);
    }

    /**
     * Enables pretty-printed JSON output.
     */
    public function withPrettyPrint(bool $enable = true): static
    {
        if ($enable) {
            $currentOptions = $this->context['json_encode_options'] ?? 0;
            return $this->withJsonEncodeOptions($currentOptions | JSON_PRETTY_PRINT);
        }

        return $this;
    }

    /**
     * Preserves zero fraction in floats (1.0 instead of 1).
     */
    public function withPreserveZeroFraction(bool $enable = true): static
    {
        if ($enable) {
            $currentOptions = $this->context['json_encode_options'] ?? 0;
            return $this->withJsonEncodeOptions($currentOptions | JSON_PRESERVE_ZERO_FRACTION);
        }

        return $this;
    }

    /**
     * Prevents escaping of unicode characters.
     */
    public function withUnescapedUnicode(bool $enable = true): static
    {
        if ($enable) {
            $currentOptions = $this->context['json_encode_options'] ?? 0;
            return $this->withJsonEncodeOptions($currentOptions | JSON_UNESCAPED_UNICODE);
        }

        return $this;
    }

    /**
     * Prevents escaping of forward slashes.
     */
    public function withUnescapedSlashes(bool $enable = true): static
    {
        if ($enable) {
            $currentOptions = $this->context['json_encode_options'] ?? 0;
            return $this->withJsonEncodeOptions($currentOptions | JSON_UNESCAPED_SLASHES);
        }

        return $this;
    }
}
