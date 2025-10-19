<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests;

use Flaphl\Element\Reissue\Encoder\JsonEncoder;
use Flaphl\Element\Reissue\Encoder\XmlEncoder;
use Flaphl\Element\Reissue\Normalizer\ArrayNormalizer;
use Flaphl\Element\Reissue\Normalizer\DateTimeNormalizer;
use Flaphl\Element\Reissue\Normalizer\ObjectNormalizer;
use Flaphl\Element\Reissue\Reissue;
use Flaphl\Element\Reissue\ReissueAwareInterface;
use PHPUnit\Framework\TestCase;

class ReissueTest extends TestCase
{
    private Reissue $reissue;

    protected function setUp(): void
    {
        $this->reissue = new Reissue(
            normalizers: [
                new DateTimeNormalizer(),
                new ArrayNormalizer(),
                new ObjectNormalizer(),
            ],
            encoders: [
                new JsonEncoder(),
                new XmlEncoder(),
            ]
        );
    }

    public function testReissueSimpleArray(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $json = $this->reissue->reissue($data, 'json');

        $this->assertJson($json);
        $this->assertStringContainsString('John', $json);
        $this->assertStringContainsString('30', $json);
    }

    public function testDeissueSimpleArray(): void
    {
        $json = '{"name":"Jane","age":25}';
        $data = $this->reissue->deissue($json, 'array', 'json');

        $this->assertIsArray($data);
        $this->assertEquals('Jane', $data['name']);
        $this->assertEquals(25, $data['age']);
    }

    public function testReissueObject(): void
    {
        $person = new TestPerson('Alice', 28);
        $json = $this->reissue->reissue($person, 'json');

        $this->assertJson($json);
        $this->assertStringContainsString('Alice', $json);
        $this->assertStringContainsString('28', $json);
    }

    public function testDeissueObject(): void
    {
        $json = '{"name":"Bob","age":35}';
        $person = $this->reissue->deissue($json, TestPerson::class, 'json');

        $this->assertInstanceOf(TestPerson::class, $person);
        $this->assertEquals('Bob', $person->name);
        $this->assertEquals(35, $person->age);
    }

    public function testReissueDateTime(): void
    {
        $date = new \DateTime('2025-10-19 12:00:00', new \DateTimeZone('UTC'));
        $json = $this->reissue->reissue($date, 'json');

        $this->assertJson($json);
        $this->assertStringContainsString('2025-10-19', $json);
    }

    public function testDeissueDateTime(): void
    {
        $json = '"2025-10-19T12:00:00+00:00"';
        $date = $this->reissue->deissue($json, \DateTime::class, 'json');

        $this->assertInstanceOf(\DateTime::class, $date);
        $this->assertEquals('2025-10-19', $date->format('Y-m-d'));
    }

    public function testReissueXml(): void
    {
        $data = ['user' => ['name' => 'Charlie', 'age' => 40]];
        $xml = $this->reissue->reissue($data, 'xml');

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('Charlie', $xml);
        $this->assertStringContainsString('40', $xml);
    }

    public function testDeissueXml(): void
    {
        $xml = '<?xml version="1.0"?><response><name>David</name><age>45</age></response>';
        $data = $this->reissue->deissue($xml, 'array', 'xml');

        $this->assertIsArray($data);
        $this->assertEquals('David', $data['name']);
        $this->assertEquals('45', $data['age']);
    }

    public function testReissueNestedObject(): void
    {
        $address = new TestAddress('123 Main St', 'Springfield');
        $person = new TestPersonWithAddress('Eve', 32, $address);
        
        $json = $this->reissue->reissue($person, 'json');

        $this->assertJson($json);
        $this->assertStringContainsString('Eve', $json);
        $this->assertStringContainsString('123 Main St', $json);
        $this->assertStringContainsString('Springfield', $json);
    }

    public function testDeissueNestedObject(): void
    {
        $json = '{"name":"Frank","age":38,"address":{"street":"456 Oak Ave","city":"Shelbyville"}}';
        $person = $this->reissue->deissue($json, TestPersonWithAddress::class, 'json');

        $this->assertInstanceOf(TestPersonWithAddress::class, $person);
        $this->assertEquals('Frank', $person->name);
        $this->assertEquals(38, $person->age);
        $this->assertInstanceOf(TestAddress::class, $person->address);
        $this->assertEquals('456 Oak Ave', $person->address->street);
        $this->assertEquals('Shelbyville', $person->address->city);
    }

    public function testJsonEncoderWithOptions(): void
    {
        $encoder = new JsonEncoder();
        $data = ['name' => 'Test'];
        
        $json = $encoder->encode($data, 'json');
        $this->assertJson($json);
        
        $decoded = $encoder->decode($json, 'json');
        $this->assertEquals($data, $decoded);
    }

    public function testXmlEncoderWithOptions(): void
    {
        $encoder = new XmlEncoder();
        $data = ['name' => 'Test'];
        
        $xml = $encoder->encode($data, 'xml', ['xml_root_node_name' => 'root']);
        $this->assertStringContainsString('<root>', $xml);
        $this->assertStringContainsString('Test', $xml);
    }

    public function testSupportsReissue(): void
    {
        $this->assertTrue($this->reissue->supportsReissue([], 'json'));
        $this->assertTrue($this->reissue->supportsReissue([], 'xml'));
        $this->assertFalse($this->reissue->supportsReissue([], 'unsupported'));
    }

    public function testSupportsDeissue(): void
    {
        $this->assertTrue($this->reissue->supportsDeissue('{}', 'array', 'json'));
        $this->assertTrue($this->reissue->supportsDeissue('<xml/>', 'array', 'xml'));
        $this->assertFalse($this->reissue->supportsDeissue('', 'array', 'unsupported'));
    }

    public function testReissueAwareInterface(): void
    {
        $aware = new TestReissueAware('Test');
        
        $json = $this->reissue->reissue($aware, 'json');
        $this->assertTrue($aware->beforeReissueCalled);

        $decoded = $this->reissue->deissue($json, TestReissueAware::class, 'json');
        $this->assertInstanceOf(TestReissueAware::class, $decoded);
        $this->assertTrue($decoded->afterDeissueCalled);
    }

    public function testArrayNormalizer(): void
    {
        $normalizer = new ArrayNormalizer();
        $data = ['a' => 1, 'b' => 2];

        $this->assertTrue($normalizer->supportsNormalization($data));
        $this->assertEquals($data, $normalizer->normalize($data));

        $this->assertTrue($normalizer->supportsDenormalization($data, 'array'));
        $this->assertEquals($data, $normalizer->denormalize($data, 'array'));
    }

    public function testDateTimeNormalizer(): void
    {
        $normalizer = new DateTimeNormalizer();
        $date = new \DateTime('2025-10-19 12:00:00');

        $this->assertTrue($normalizer->supportsNormalization($date));
        $normalized = $normalizer->normalize($date);
        $this->assertIsString($normalized);

        $this->assertTrue($normalizer->supportsDenormalization($normalized, \DateTime::class));
        $denormalized = $normalizer->denormalize($normalized, \DateTime::class);
        $this->assertInstanceOf(\DateTime::class, $denormalized);
    }

    public function testObjectNormalizer(): void
    {
        $normalizer = new ObjectNormalizer();
        $person = new TestPerson('Test', 25);

        $this->assertTrue($normalizer->supportsNormalization($person));
        $normalized = $normalizer->normalize($person);
        $this->assertIsArray($normalized);
        $this->assertEquals('Test', $normalized['name']);

        $this->assertTrue($normalizer->supportsDenormalization($normalized, TestPerson::class));
        $denormalized = $normalizer->denormalize($normalized, TestPerson::class);
        $this->assertInstanceOf(TestPerson::class, $denormalized);
        $this->assertEquals('Test', $denormalized->name);
    }
}

// Test classes
class TestPerson
{
    public function __construct(
        public string $name,
        public int $age
    ) {
    }
}

class TestAddress
{
    public function __construct(
        public string $street,
        public string $city
    ) {
    }
}

class TestPersonWithAddress
{
    public function __construct(
        public string $name,
        public int $age,
        public TestAddress $address
    ) {
    }
}

class TestReissueAware implements ReissueAwareInterface
{
    public bool $beforeReissueCalled = false;
    public bool $afterDeissueCalled = false;

    public function __construct(public string $value)
    {
    }

    public function beforeReissue(array $context = []): void
    {
        $this->beforeReissueCalled = true;
    }

    public function afterDeissue(array $context = []): void
    {
        $this->afterDeissueCalled = true;
    }
}
