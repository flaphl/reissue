<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Context;

/**
 * Builder for reissue (serialization/deserialization) contexts.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ReissueContextBuilder implements ContextBuilderInterface
{
    use ContextBuilderTrait;

    /**
     * Creates a new context builder instance.
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * Sets JSON encoding options.
     *
     * @param int $options JSON encoding options
     *
     * @return static
     */
    public function withJsonEncodeOptions(int $options): static
    {
        return $this->with('json_encode_options', $options);
    }

    /**
     * Sets JSON decoding options.
     *
     * @param int $options JSON decoding options
     *
     * @return static
     */
    public function withJsonDecodeOptions(int $options): static
    {
        return $this->with('json_decode_options', $options);
    }

    /**
     * Sets whether JSON should be decoded as associative array.
     *
     * @param bool $associative Whether to decode as array
     *
     * @return static
     */
    public function withJsonDecodeAssociative(bool $associative): static
    {
        return $this->with('json_decode_associative', $associative);
    }

    /**
     * Sets XML root node name.
     *
     * @param string $name Root node name
     *
     * @return static
     */
    public function withXmlRootNodeName(string $name): static
    {
        return $this->with('xml_root_node_name', $name);
    }

    /**
     * Sets XML encoding.
     *
     * @param string $encoding XML encoding (e.g., 'UTF-8')
     *
     * @return static
     */
    public function withXmlEncoding(string $encoding): static
    {
        return $this->with('xml_encoding', $encoding);
    }

    /**
     * Sets XML version.
     *
     * @param string $version XML version (e.g., '1.0')
     *
     * @return static
     */
    public function withXmlVersion(string $version): static
    {
        return $this->with('xml_version', $version);
    }

    /**
     * Sets whether XML output should be formatted.
     *
     * @param bool $format Whether to format output
     *
     * @return static
     */
    public function withXmlFormatOutput(bool $format): static
    {
        return $this->with('xml_format_output', $format);
    }

    /**
     * Sets the DateTime format.
     *
     * @param string $format DateTime format string
     *
     * @return static
     */
    public function withDateTimeFormat(string $format): static
    {
        return $this->with('datetime_format', $format);
    }

    /**
     * Sets whether to skip null values during normalization.
     *
     * @param bool $skip Whether to skip null values
     *
     * @return static
     */
    public function withSkipNullValues(bool $skip): static
    {
        return $this->with('skip_null_values', $skip);
    }

    /**
     * Sets the current depth level (internal use).
     *
     * @param int $depth Current depth
     *
     * @return static
     */
    public function withCurrentDepth(int $depth): static
    {
        return $this->with('current_depth', $depth);
    }
}
