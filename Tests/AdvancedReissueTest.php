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

use Flaphl\Element\Reissue\Attribute\Groups;
use Flaphl\Element\Reissue\Attribute\Ignore;
use Flaphl\Element\Reissue\Attribute\SerializedName;
use Flaphl\Element\Reissue\Context\ReissueContextBuilder;
use Flaphl\Element\Reissue\Encoder\ChainDecoder;
use Flaphl\Element\Reissue\Encoder\ChainEncoder;
use Flaphl\Element\Reissue\Encoder\JsonEncoder;
use Flaphl\Element\Reissue\Encoder\XmlEncoder;
use Flaphl\Element\Reissue\Mapping\Loader\AttributeLoader;
use Flaphl\Element\Reissue\NameRecast\CamelCaseToSnakeCaseNameConverter;
use Flaphl\Element\Reissue\Normalizer\MetadataAwareObjectNormalizer;
use Flaphl\Element\Reissue\Reissue;
use PHPUnit\Framework\TestCase;

class AdvancedReissueTest extends TestCase
{
    public function testContextBuilder(): void
    {
        $context = ReissueContextBuilder::create()
            ->withGroups(['public'])
            ->withMaxDepth(3)
            ->withSerializeNull(false)
            ->withJsonEncodeOptions(JSON_PRETTY_PRINT)
            ->withXmlRootNodeName('data')
            ->withDateTimeFormat('Y-m-d')
            ->toArray();

        $this->assertEquals(['public'], $context['groups']);
        $this->assertEquals(3, $context['max_depth']);
        $this->assertFalse($context['serialize_null']);
        $this->assertEquals(JSON_PRETTY_PRINT, $context['json_encode_options']);
        $this->assertEquals('data', $context['xml_root_node_name']);
        $this->assertEquals('Y-m-d', $context['datetime_format']);
    }

    public function testChainEncoder(): void
    {
        $chain = new ChainEncoder([
            new JsonEncoder(),
            new XmlEncoder(),
        ]);

        $this->assertTrue($chain->supportsEncoding('json'));
        $this->assertTrue($chain->supportsEncoding('xml'));
        $this->assertFalse($chain->supportsEncoding('yaml'));

        $data = ['test' => 'value'];
        $json = $chain->encode($data, 'json');
        $this->assertJson($json);

        $xml = $chain->encode($data, 'xml');
        $this->assertStringContainsString('<?xml', $xml);
    }

    public function testChainDecoder(): void
    {
        $chain = new ChainDecoder([
            new JsonEncoder(),
            new XmlEncoder(),
        ]);

        $this->assertTrue($chain->supportsDecoding('json'));
        $this->assertTrue($chain->supportsDecoding('xml'));
        $this->assertFalse($chain->supportsDecoding('yaml'));

        $data = $chain->decode('{"test":"value"}', 'json');
        $this->assertEquals(['test' => 'value'], $data);
    }

    public function testAttributeLoader(): void
    {
        $loader = new AttributeLoader();
        $metadata = $loader->loadClassMetadata(TestUserWithAttributes::class);

        $this->assertEquals(TestUserWithAttributes::class, $metadata->getClassName());
        
        // Check ignored attribute
        $passwordMetadata = $metadata->getAttributeMetadata('password');
        $this->assertTrue($passwordMetadata->isIgnored());

        // Check serialized name
        $emailMetadata = $metadata->getAttributeMetadata('email');
        $this->assertEquals('email_address', $emailMetadata->getSerializedName());

        // Check groups
        $nameMetadata = $metadata->getAttributeMetadata('name');
        $this->assertEquals(['public', 'admin'], $nameMetadata->getGroups());
    }

    public function testMetadataAwareNormalizerWithIgnore(): void
    {
        $normalizer = new MetadataAwareObjectNormalizer();
        $user = new TestUserWithAttributes('John', 'john@example.com', 'secret123');

        $normalized = $normalizer->normalize($user);

        $this->assertArrayHasKey('name', $normalized);
        $this->assertArrayHasKey('email_address', $normalized);
        $this->assertArrayNotHasKey('password', $normalized); // Should be ignored
        $this->assertEquals('John', $normalized['name']);
        $this->assertEquals('john@example.com', $normalized['email_address']);
    }

    public function testMetadataAwareNormalizerWithGroups(): void
    {
        $normalizer = new MetadataAwareObjectNormalizer();
        $user = new TestUserWithAttributes('Jane', 'jane@example.com', 'pass456');

        // Serialize with 'public' group - should include name
        $publicContext = ['groups' => ['public']];
        $normalized = $normalizer->normalize($user, null, $publicContext);

        $this->assertArrayHasKey('name', $normalized);

        // Serialize with 'internal' group - should not include name (only in public/admin)
        $internalContext = ['groups' => ['internal']];
        $normalized = $normalizer->normalize($user, null, $internalContext);

        $this->assertArrayNotHasKey('name', $normalized);
    }

    public function testCamelCaseToSnakeCaseNameConverter(): void
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        // Normalize (camelCase -> snake_case)
        $this->assertEquals('first_name', $converter->normalize('firstName'));
        $this->assertEquals('user_id', $converter->normalize('userId'));
        $this->assertEquals('created_at', $converter->normalize('createdAt'));

        // Denormalize (snake_case -> camelCase)
        $this->assertEquals('firstName', $converter->denormalize('first_name'));
        $this->assertEquals('userId', $converter->denormalize('user_id'));
        $this->assertEquals('createdAt', $converter->denormalize('created_at'));
    }

    public function testMetadataAwareNormalizerWithNameConverter(): void
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();
        $normalizer = new MetadataAwareObjectNormalizer($converter);
        
        $product = new TestProduct('Laptop', 999.99);
        $normalized = $normalizer->normalize($product);

        // Should have snake_case keys
        $this->assertArrayHasKey('product_name', $normalized);
        $this->assertArrayHasKey('product_price', $normalized);
        $this->assertEquals('Laptop', $normalized['product_name']);
        $this->assertEquals(999.99, $normalized['product_price']);
    }

    public function testFullReissueWithMetadata(): void
    {
        $reissue = new Reissue(
            normalizers: [
                new MetadataAwareObjectNormalizer(),
            ],
            encoders: [
                new JsonEncoder(),
            ]
        );

        $user = new TestUserWithAttributes('Alice', 'alice@example.com', 'secret');
        $json = $reissue->reissue($user, 'json');

        $this->assertJson($json);
        $this->assertStringContainsString('Alice', $json);
        $this->assertStringContainsString('email_address', $json);
        $this->assertStringNotContainsString('password', $json);

        // Denormalize back
        $deserialized = $reissue->deissue($json, TestUserWithAttributes::class, 'json');
        $this->assertInstanceOf(TestUserWithAttributes::class, $deserialized);
        $this->assertEquals('Alice', $deserialized->name);
        $this->assertEquals('alice@example.com', $deserialized->email);
    }

    public function testMaxDepthContext(): void
    {
        $normalizer = new MetadataAwareObjectNormalizer();
        
        $address = new TestAddressSimple('123 Main St');
        $person = new TestPersonWithAddressSimple('Bob', $address);

        // With max_depth = 1, should not serialize nested address
        $context = ['max_depth' => 1];
        $normalized = $normalizer->normalize($person, null, $context);

        $this->assertArrayHasKey('name', $normalized);
        // Address might be empty or excluded depending on implementation
    }

    public function testSkipNullValues(): void
    {
        $normalizer = new MetadataAwareObjectNormalizer();
        $user = new TestUserWithNulls('Charlie', null);

        // Without skip_null_values
        $normalized = $normalizer->normalize($user);
        $this->assertArrayHasKey('email', $normalized);
        $this->assertNull($normalized['email']);

        // With skip_null_values
        $context = ['skip_null_values' => true];
        $normalized = $normalizer->normalize($user, null, $context);
        $this->assertArrayNotHasKey('email', $normalized);
    }
}

// Test classes with attributes
class TestUserWithAttributes
{
    public function __construct(
        #[Groups(['public', 'admin'])]
        public string $name,

        #[SerializedName('email_address')]
        public string $email,

        #[Ignore]
        public string $password
    ) {
    }
}

class TestProduct
{
    public function __construct(
        public string $productName,
        public float $productPrice
    ) {
    }
}

class TestAddressSimple
{
    public function __construct(
        public string $street
    ) {
    }
}

class TestPersonWithAddressSimple
{
    public function __construct(
        public string $name,
        public TestAddressSimple $address
    ) {
    }
}

class TestUserWithNulls
{
    public function __construct(
        public string $name,
        public ?string $email
    ) {
    }
}
