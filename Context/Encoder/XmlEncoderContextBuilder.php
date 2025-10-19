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
 * Context builder for XML encoder specific options.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class XmlEncoderContextBuilder implements ContextBuilderInterface
{
    use ContextBuilderTrait;

    /**
     * Sets the XML root node name.
     *
     * @param string $name The root node name
     */
    public function withRootNodeName(string $name): static
    {
        return $this->with('xml_root_node_name', $name);
    }

    /**
     * Sets the XML version.
     *
     * @param string $version The XML version (e.g., '1.0')
     */
    public function withXmlVersion(string $version): static
    {
        return $this->with('xml_version', $version);
    }

    /**
     * Sets the XML encoding.
     *
     * @param string $encoding The XML encoding (e.g., 'UTF-8')
     */
    public function withXmlEncoding(string $encoding): static
    {
        return $this->with('xml_encoding', $encoding);
    }

    /**
     * Enables or disables formatted XML output.
     */
    public function withFormatOutput(bool $format = true): static
    {
        return $this->with('xml_format_output', $format);
    }

    /**
     * Sets whether to use standalone declaration in XML.
     */
    public function withStandalone(bool $standalone = true): static
    {
        return $this->with('xml_standalone', $standalone);
    }

    /**
     * Sets the name for attributes when converting from array.
     *
     * @param string $prefix The prefix for attribute keys
     */
    public function withAttributePrefix(string $prefix): static
    {
        return $this->with('xml_attribute_prefix', $prefix);
    }

    /**
     * Sets whether to use CDATA sections for text content.
     */
    public function withCdata(bool $useCdata = true): static
    {
        return $this->with('xml_use_cdata', $useCdata);
    }
}
