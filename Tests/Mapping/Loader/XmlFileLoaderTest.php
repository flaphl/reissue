<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Mapping\Loader;

use Flaphl\Element\Reissue\Exception\InvalidArgumentException;
use Flaphl\Element\Reissue\Mapping\Loader\XmlFileLoader;
use PHPUnit\Framework\TestCase;

class XmlFileLoaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/reissue_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }

    private function createXmlFile(string $content): string
    {
        $file = $this->tempDir . '/mapping.xml';
        file_put_contents($file, $content);
        return $file;
    }

    public function testLoadClassMetadataWithBasicProperty(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="id"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertCount(1, $attributes);
        $this->assertArrayHasKey('id', $attributes);
    }

    public function testLoadClassMetadataWithIgnoredProperty(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="password" ignore="true"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertTrue($attributes['password']->isIgnored());
    }

    public function testLoadClassMetadataWithSerializedName(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="firstName" serialized-name="first_name"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertEquals('first_name', $attributes['firstName']->getSerializedName());
    }

    public function testLoadClassMetadataWithGroups(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="email">
            <groups>
                <group>public</group>
                <group>admin</group>
            </groups>
        </property>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertEquals(['public', 'admin'], $attributes['email']->getGroups());
    }

    public function testLoadClassMetadataWithMaxDepth(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="related" max-depth="2"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertEquals(2, $attributes['related']->getMaxDepth());
    }

    public function testLoadClassMetadataWithMultipleProperties(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="id"/>
        <property name="name"/>
        <property name="email"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertCount(3, $attributes);
        $this->assertArrayHasKey('id', $attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('email', $attributes);
    }

    public function testLoadClassMetadataWithComplexConfiguration(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="User">
        <property name="id"/>
        <property name="firstName" serialized-name="first_name">
            <groups>
                <group>public</group>
            </groups>
        </property>
        <property name="password" ignore="true"/>
        <property name="children" max-depth="3">
            <groups>
                <group>family</group>
            </groups>
        </property>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('User');

        $attributes = $metadata->getAttributesMetadata();
        
        $this->assertCount(4, $attributes);
        
        // Check firstName
        $this->assertEquals('first_name', $attributes['firstName']->getSerializedName());
        $this->assertEquals(['public'], $attributes['firstName']->getGroups());
        
        // Check password
        $this->assertTrue($attributes['password']->isIgnored());
        
        // Check children
        $this->assertEquals(3, $attributes['children']->getMaxDepth());
        $this->assertEquals(['family'], $attributes['children']->getGroups());
    }

    public function testLoadClassMetadataReturnsEmptyForNonExistentClass(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="id"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('NonExistentClass');

        $this->assertCount(0, $metadata->getAttributesMetadata());
    }

    public function testLoadInvalidXmlThrowsException(): void
    {
        $xml = '<?xml version="1.0"?><invalid><unclosed>';
        
        $file = $this->createXmlFile($xml);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse XML file');
        
        $loader = new XmlFileLoader($file);
        $loader->loadClassMetadata('TestClass');
    }

    public function testLoadClassMetadataWithNamespace(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="App\\Entity\\User">
        <property name="id"/>
        <property name="username"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('App\\Entity\\User');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertCount(2, $attributes);
    }

    public function testLoadClassMetadataWithEmptyGroups(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="field">
            <groups></groups>
        </property>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertEquals([], $attributes['field']->getGroups());
    }

    public function testLoadClassMetadataIgnoreFalse(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mapping>
    <class name="TestClass">
        <property name="field" ignore="false"/>
    </class>
</mapping>
XML;

        $file = $this->createXmlFile($xml);
        $loader = new XmlFileLoader($file);
        $metadata = $loader->loadClassMetadata('TestClass');

        $attributes = $metadata->getAttributesMetadata();
        $this->assertFalse($attributes['field']->isIgnored());
    }
}
