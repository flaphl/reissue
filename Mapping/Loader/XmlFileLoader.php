<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping\Loader;

use Flaphl\Element\Reissue\Mapping\AttributeMetadata;
use Flaphl\Element\Reissue\Mapping\ClassMetadata;
use Flaphl\Element\Reissue\Exception\InvalidArgumentException;

/**
 * Loads mapping metadata from XML files.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class XmlFileLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(string $className): ClassMetadata
    {
        $metadata = new ClassMetadata($className);

        $xml = simplexml_load_file($this->file);
        
        if ($xml === false) {
            throw new InvalidArgumentException(sprintf('Unable to parse XML file "%s".', $this->file));
        }

        // Find the class element
        $classElements = $xml->xpath(sprintf('//class[@name="%s"]', $className));
        
        if (empty($classElements)) {
            return $metadata;
        }

        $classElement = $classElements[0];

        // Load properties
        foreach ($classElement->property ?? [] as $property) {
            $attributeName = (string) $property['name'];
            $attributeMetadata = new AttributeMetadata($attributeName);

            // Check if property should be ignored
            if (isset($property['ignore']) && (string) $property['ignore'] === 'true') {
                $attributeMetadata->setIgnored(true);
            }

            // Set serialized name
            if (isset($property['serialized-name'])) {
                $attributeMetadata->setSerializedName((string) $property['serialized-name']);
            }

            // Set groups
            if (isset($property->groups)) {
                $groups = [];
                foreach ($property->groups->group as $group) {
                    $groups[] = (string) $group;
                }
                $attributeMetadata->setGroups($groups);
            }

            // Set max depth
            if (isset($property['max-depth'])) {
                $attributeMetadata->setMaxDepth((int) $property['max-depth']);
            }

            $metadata->addAttributeMetadata($attributeMetadata);
        }

        return $metadata;
    }
}
