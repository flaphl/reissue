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
 * Encodes and decodes XML data.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class XmlEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'xml';
    private const ROOT_NODE_NAME = 'response';

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        $rootNodeName = $context['xml_root_node_name'] ?? self::ROOT_NODE_NAME;
        $encoding = $context['xml_encoding'] ?? 'UTF-8';
        $version = $context['xml_version'] ?? '1.0';

        $dom = new \DOMDocument($version, $encoding);
        $dom->formatOutput = $context['xml_format_output'] ?? true;

        $root = $dom->createElement($rootNodeName);
        $dom->appendChild($root);

        $this->buildXml($dom, $root, $data);

        $xml = $dom->saveXML();
        
        if ($xml === false) {
            throw new InvalidArgumentException('Failed to generate XML.');
        }

        return $xml;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        try {
            $dom = new \DOMDocument();
            
            if (!$dom->loadXML($data, LIBXML_NONET | LIBXML_NOBLANKS)) {
                throw new InvalidArgumentException('Invalid XML: ' . implode(', ', $this->getXmlErrors()));
            }

            return $this->parseXml($dom->documentElement);
        } finally {
            libxml_use_internal_errors($internalErrors);
            libxml_disable_entity_loader($disableEntities);
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

    /**
     * Builds XML from array data.
     */
    private function buildXml(\DOMDocument $dom, \DOMElement $parent, mixed $data): void
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_int($key)) {
                    $key = 'item';
                }

                $node = $dom->createElement($key);
                $parent->appendChild($node);

                if (is_array($value) || is_object($value)) {
                    $this->buildXml($dom, $node, $value);
                } else {
                    $node->appendChild($dom->createTextNode((string) $value));
                }
            }
        } elseif (is_object($data)) {
            foreach (get_object_vars($data) as $key => $value) {
                $node = $dom->createElement($key);
                $parent->appendChild($node);

                if (is_array($value) || is_object($value)) {
                    $this->buildXml($dom, $node, $value);
                } else {
                    $node->appendChild($dom->createTextNode((string) $value));
                }
            }
        } else {
            $parent->appendChild($dom->createTextNode((string) $data));
        }
    }

    /**
     * Parses XML into array.
     */
    private function parseXml(\DOMElement $element): array|string
    {
        $result = [];

        if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $value = $this->parseXml($child);
                    
                    if (isset($result[$child->nodeName])) {
                        if (!is_array($result[$child->nodeName]) || !isset($result[$child->nodeName][0])) {
                            $result[$child->nodeName] = [$result[$child->nodeName]];
                        }
                        $result[$child->nodeName][] = $value;
                    } else {
                        $result[$child->nodeName] = $value;
                    }
                } elseif ($child instanceof \DOMText && trim($child->nodeValue) !== '') {
                    return trim($child->nodeValue);
                }
            }
        }

        return $result ?: '';
    }

    /**
     * Gets XML errors as array.
     */
    private function getXmlErrors(): array
    {
        $errors = [];
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (line %s)', $error->level, $error->code, trim($error->message), $error->line);
        }
        libxml_clear_errors();

        return $errors;
    }
}
