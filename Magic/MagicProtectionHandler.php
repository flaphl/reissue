<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Magic;

/**
 * Handles serialization of objects with magic methods (__sleep, __wakeup, etc.).
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class MagicProtectionHandler
{
    /**
     * Checks if a class has __sleep method.
     */
    public function hasSleepMethod(string|object $class): bool
    {
        $className = is_object($class) ? get_class($class) : $class;
        return method_exists($className, '__sleep');
    }

    /**
     * Checks if a class has __wakeup method.
     */
    public function hasWakeupMethod(string|object $class): bool
    {
        $className = is_object($class) ? get_class($class) : $class;
        return method_exists($className, '__wakeup');
    }

    /**
     * Checks if a class has __serialize method (PHP 7.4+).
     */
    public function hasSerializeMethod(string|object $class): bool
    {
        $className = is_object($class) ? get_class($class) : $class;
        return method_exists($className, '__serialize');
    }

    /**
     * Checks if a class has __unserialize method (PHP 7.4+).
     */
    public function hasUnserializeMethod(string|object $class): bool
    {
        $className = is_object($class) ? get_class($class) : $class;
        return method_exists($className, '__unserialize');
    }

    /**
     * Invokes __sleep on an object if it exists.
     *
     * @return string[]|null Array of property names to serialize, or null if __sleep doesn't exist
     */
    public function invokeSleep(object $object): ?array
    {
        if (!$this->hasSleepMethod($object)) {
            return null;
        }

        return $object->__sleep();
    }

    /**
     * Invokes __wakeup on an object if it exists.
     */
    public function invokeWakeup(object $object): void
    {
        if ($this->hasWakeupMethod($object)) {
            $object->__wakeup();
        }
    }

    /**
     * Invokes __serialize on an object if it exists.
     *
     * @return array<string, mixed>|null Serialized data, or null if __serialize doesn't exist
     */
    public function invokeSerialize(object $object): ?array
    {
        if (!$this->hasSerializeMethod($object)) {
            return null;
        }

        return $object->__serialize();
    }

    /**
     * Invokes __unserialize on an object if it exists.
     *
     * @param array<string, mixed> $data The data to unserialize
     */
    public function invokeUnserialize(object $object, array $data): void
    {
        if ($this->hasUnserializeMethod($object)) {
            $object->__unserialize($data);
        }
    }

    /**
     * Determines which properties should be serialized for an object.
     *
     * @return string[]|null Array of property names, or null to serialize all properties
     */
    public function getPropertiesToSerialize(object $object): ?array
    {
        // Check for __serialize first (newer PHP 7.4+ method)
        if ($this->hasSerializeMethod($object)) {
            $data = $this->invokeSerialize($object);
            return $data !== null ? array_keys($data) : null;
        }

        // Fall back to __sleep
        return $this->invokeSleep($object);
    }

    /**
     * Checks if an object needs magic method handling during serialization.
     */
    public function needsMagicHandling(object $object): bool
    {
        return $this->hasSleepMethod($object) 
            || $this->hasWakeupMethod($object)
            || $this->hasSerializeMethod($object)
            || $this->hasUnserializeMethod($object);
    }
}
