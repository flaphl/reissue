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

use Flaphl\Element\Reissue\Attribute\Groups;
use Flaphl\Element\Reissue\Attribute\Ignore;
use Flaphl\Element\Reissue\Attribute\MaxDepth;
use Flaphl\Element\Reissue\Attribute\SerializedName;
use Flaphl\Element\Reissue\Mapping\AttributeMetadata;
use Flaphl\Element\Reissue\Mapping\ClassMetadata;

/**
 * Loads metadata from PHP 8 attributes.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class AttributeLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(string $className): ClassMetadata
    {
        $reflection = new \ReflectionClass($className);
        $classMetadata = new ClassMetadata($className);

        // Load class-level attributes
        $this->loadClassAttributes($reflection, $classMetadata);

        // Load property attributes
        foreach ($reflection->getProperties() as $property) {
            $attributeMetadata = new AttributeMetadata($property->getName());
            $this->loadPropertyAttributes($property, $attributeMetadata);
            $classMetadata->addAttributeMetadata($attributeMetadata);
        }

        return $classMetadata;
    }

    /**
     * Loads class-level attributes.
     */
    private function loadClassAttributes(\ReflectionClass $reflection, ClassMetadata $metadata): void
    {
        // Groups
        $groupsAttrs = $reflection->getAttributes(Groups::class);
        if (!empty($groupsAttrs)) {
            $groups = $groupsAttrs[0]->newInstance();
            $metadata->setGroups($groups->groups);
        }

        // MaxDepth
        $maxDepthAttrs = $reflection->getAttributes(MaxDepth::class);
        if (!empty($maxDepthAttrs)) {
            $maxDepth = $maxDepthAttrs[0]->newInstance();
            $metadata->setMaxDepth($maxDepth->depth);
        }
    }

    /**
     * Loads property-level attributes.
     */
    private function loadPropertyAttributes(\ReflectionProperty $property, AttributeMetadata $metadata): void
    {
        // Ignore
        $ignoreAttrs = $property->getAttributes(Ignore::class);
        if (!empty($ignoreAttrs)) {
            $metadata->setIgnored(true);
        }

        // SerializedName
        $nameAttrs = $property->getAttributes(SerializedName::class);
        if (!empty($nameAttrs)) {
            $serializedName = $nameAttrs[0]->newInstance();
            $metadata->setSerializedName($serializedName->name);
        }

        // Groups
        $groupsAttrs = $property->getAttributes(Groups::class);
        if (!empty($groupsAttrs)) {
            $groups = $groupsAttrs[0]->newInstance();
            $metadata->setGroups($groups->groups);
        }

        // MaxDepth
        $maxDepthAttrs = $property->getAttributes(MaxDepth::class);
        if (!empty($maxDepthAttrs)) {
            $maxDepth = $maxDepthAttrs[0]->newInstance();
            $metadata->setMaxDepth($maxDepth->depth);
        }
    }
}
