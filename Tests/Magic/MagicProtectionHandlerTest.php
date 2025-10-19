<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Magic;

use Flaphl\Element\Reissue\Magic\MagicProtectionHandler;
use PHPUnit\Framework\TestCase;

// Test fixtures
class ObjectWithSleep
{
    public $property1 = 'value1';
    public $property2 = 'value2';
    
    public function __sleep(): array
    {
        return ['property1']; // Only serialize property1
    }
}

class ObjectWithWakeup
{
    public $initialized = false;
    
    public function __wakeup(): void
    {
        $this->initialized = true;
    }
}

class ObjectWithSerialize
{
    public $data = ['key' => 'value'];
    
    public function __serialize(): array
    {
        return ['serialized_data' => $this->data];
    }
}

class ObjectWithUnserialize
{
    public $restored = false;
    
    public function __unserialize(array $data): void
    {
        $this->restored = true;
    }
}

class ObjectWithAllMagicMethods
{
    public function __sleep(): array { return []; }
    public function __wakeup(): void {}
    public function __serialize(): array { return []; }
    public function __unserialize(array $data): void {}
}

class ObjectWithoutMagicMethods
{
    public $property = 'value';
}

class MagicProtectionHandlerTest extends TestCase
{
    private MagicProtectionHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new MagicProtectionHandler();
    }

    public function testHasSleepMethodWithString(): void
    {
        $this->assertTrue($this->handler->hasSleepMethod(ObjectWithSleep::class));
        $this->assertFalse($this->handler->hasSleepMethod(ObjectWithoutMagicMethods::class));
    }

    public function testHasSleepMethodWithObject(): void
    {
        $this->assertTrue($this->handler->hasSleepMethod(new ObjectWithSleep()));
        $this->assertFalse($this->handler->hasSleepMethod(new ObjectWithoutMagicMethods()));
    }

    public function testHasWakeupMethodWithString(): void
    {
        $this->assertTrue($this->handler->hasWakeupMethod(ObjectWithWakeup::class));
        $this->assertFalse($this->handler->hasWakeupMethod(ObjectWithoutMagicMethods::class));
    }

    public function testHasWakeupMethodWithObject(): void
    {
        $this->assertTrue($this->handler->hasWakeupMethod(new ObjectWithWakeup()));
        $this->assertFalse($this->handler->hasWakeupMethod(new ObjectWithoutMagicMethods()));
    }

    public function testHasSerializeMethodWithString(): void
    {
        $this->assertTrue($this->handler->hasSerializeMethod(ObjectWithSerialize::class));
        $this->assertFalse($this->handler->hasSerializeMethod(ObjectWithoutMagicMethods::class));
    }

    public function testHasSerializeMethodWithObject(): void
    {
        $this->assertTrue($this->handler->hasSerializeMethod(new ObjectWithSerialize()));
        $this->assertFalse($this->handler->hasSerializeMethod(new ObjectWithoutMagicMethods()));
    }

    public function testHasUnserializeMethodWithString(): void
    {
        $this->assertTrue($this->handler->hasUnserializeMethod(ObjectWithUnserialize::class));
        $this->assertFalse($this->handler->hasUnserializeMethod(ObjectWithoutMagicMethods::class));
    }

    public function testHasUnserializeMethodWithObject(): void
    {
        $this->assertTrue($this->handler->hasUnserializeMethod(new ObjectWithUnserialize()));
        $this->assertFalse($this->handler->hasUnserializeMethod(new ObjectWithoutMagicMethods()));
    }

    public function testInvokeSleepReturnsPropertyNames(): void
    {
        $object = new ObjectWithSleep();
        $properties = $this->handler->invokeSleep($object);

        $this->assertIsArray($properties);
        $this->assertEquals(['property1'], $properties);
    }

    public function testInvokeSleepReturnsNullWhenNoMethod(): void
    {
        $object = new ObjectWithoutMagicMethods();
        $properties = $this->handler->invokeSleep($object);

        $this->assertNull($properties);
    }

    public function testInvokeWakeup(): void
    {
        $object = new ObjectWithWakeup();
        $this->assertFalse($object->initialized);

        $this->handler->invokeWakeup($object);

        $this->assertTrue($object->initialized);
    }

    public function testInvokeWakeupDoesNothingWhenNoMethod(): void
    {
        $object = new ObjectWithoutMagicMethods();
        
        // Should not throw exception
        $this->handler->invokeWakeup($object);
        $this->assertTrue(true); // Reached here without error
    }

    public function testInvokeSerializeReturnsData(): void
    {
        $object = new ObjectWithSerialize();
        $data = $this->handler->invokeSerialize($object);

        $this->assertIsArray($data);
        $this->assertEquals(['serialized_data' => ['key' => 'value']], $data);
    }

    public function testInvokeSerializeReturnsNullWhenNoMethod(): void
    {
        $object = new ObjectWithoutMagicMethods();
        $data = $this->handler->invokeSerialize($object);

        $this->assertNull($data);
    }

    public function testInvokeUnserialize(): void
    {
        $object = new ObjectWithUnserialize();
        $this->assertFalse($object->restored);

        $this->handler->invokeUnserialize($object, ['test' => 'data']);

        $this->assertTrue($object->restored);
    }

    public function testInvokeUnserializeDoesNothingWhenNoMethod(): void
    {
        $object = new ObjectWithoutMagicMethods();
        
        // Should not throw exception
        $this->handler->invokeUnserialize($object, []);
        $this->assertTrue(true); // Reached here without error
    }

    public function testGetPropertiesToSerializeWithSerializeMethod(): void
    {
        $object = new ObjectWithSerialize();
        $properties = $this->handler->getPropertiesToSerialize($object);

        $this->assertEquals(['serialized_data'], $properties);
    }

    public function testGetPropertiesToSerializeWithSleepMethod(): void
    {
        $object = new ObjectWithSleep();
        $properties = $this->handler->getPropertiesToSerialize($object);

        $this->assertEquals(['property1'], $properties);
    }

    public function testGetPropertiesToSerializeWithNoMethods(): void
    {
        $object = new ObjectWithoutMagicMethods();
        $properties = $this->handler->getPropertiesToSerialize($object);

        $this->assertNull($properties);
    }

    public function testGetPropertiesToSerializePrefersSerializeOverSleep(): void
    {
        $object = new ObjectWithAllMagicMethods();
        $properties = $this->handler->getPropertiesToSerialize($object);

        // Should use __serialize, not __sleep
        $this->assertIsArray($properties);
        $this->assertEquals([], $properties);
    }

    public function testNeedsMagicHandlingReturnsTrueForSleep(): void
    {
        $object = new ObjectWithSleep();
        $this->assertTrue($this->handler->needsMagicHandling($object));
    }

    public function testNeedsMagicHandlingReturnsTrueForWakeup(): void
    {
        $object = new ObjectWithWakeup();
        $this->assertTrue($this->handler->needsMagicHandling($object));
    }

    public function testNeedsMagicHandlingReturnsTrueForSerialize(): void
    {
        $object = new ObjectWithSerialize();
        $this->assertTrue($this->handler->needsMagicHandling($object));
    }

    public function testNeedsMagicHandlingReturnsTrueForUnserialize(): void
    {
        $object = new ObjectWithUnserialize();
        $this->assertTrue($this->handler->needsMagicHandling($object));
    }

    public function testNeedsMagicHandlingReturnsFalseForNoMethods(): void
    {
        $object = new ObjectWithoutMagicMethods();
        $this->assertFalse($this->handler->needsMagicHandling($object));
    }

    public function testNeedsMagicHandlingReturnsTrueForAnyMethod(): void
    {
        $object = new ObjectWithAllMagicMethods();
        $this->assertTrue($this->handler->needsMagicHandling($object));
    }

    public function testHandlesStdClass(): void
    {
        $object = new \stdClass();
        
        $this->assertFalse($this->handler->hasSleepMethod($object));
        $this->assertFalse($this->handler->hasWakeupMethod($object));
        $this->assertFalse($this->handler->hasSerializeMethod($object));
        $this->assertFalse($this->handler->hasUnserializeMethod($object));
        $this->assertFalse($this->handler->needsMagicHandling($object));
    }

    public function testInvokeSleepReturnsEmptyArray(): void
    {
        $object = new class {
            public function __sleep(): array {
                return [];
            }
        };

        $properties = $this->handler->invokeSleep($object);
        $this->assertEquals([], $properties);
    }

    public function testInvokeSerializeReturnsEmptyArray(): void
    {
        $object = new class {
            public function __serialize(): array {
                return [];
            }
        };

        $data = $this->handler->invokeSerialize($object);
        $this->assertEquals([], $data);
    }

    public function testInvokeUnserializeWithEmptyData(): void
    {
        $called = false;
        $object = new class($called) {
            public function __construct(private &$called) {}
            
            public function __unserialize(array $data): void {
                $this->called = true;
            }
        };

        $this->handler->invokeUnserialize($object, []);
        $this->assertTrue($called);
    }
}
