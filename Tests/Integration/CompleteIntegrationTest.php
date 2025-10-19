<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Integration;

use Flaphl\Element\Reissue\Attribute\Groups;
use Flaphl\Element\Reissue\Attribute\Ignore;
use Flaphl\Element\Reissue\Attribute\MaxDepth;
use Flaphl\Element\Reissue\Attribute\SerializedName;
use Flaphl\Element\Reissue\Context\Encoder\JsonEncoderContextBuilder;
use Flaphl\Element\Reissue\Context\Encoder\XmlEncoderContextBuilder;
use Flaphl\Element\Reissue\Context\ReissueContextBuilder;
use Flaphl\Element\Reissue\DataCollector\ReissueDataCollector;
use Flaphl\Element\Reissue\Encoder\ChainEncoder;
use Flaphl\Element\Reissue\Encoder\JsonEncoder;
use Flaphl\Element\Reissue\Encoder\XmlEncoder;
use Flaphl\Element\Reissue\Magic\MagicProtectionHandler;
use Flaphl\Element\Reissue\Mapping\Atelier\CacheClassMetaDataAtelier;
use Flaphl\Element\Reissue\Mapping\Atelier\ClassMetadataAtelier;
use Flaphl\Element\Reissue\Mapping\Loader\AttributeLoader;
use Flaphl\Element\Reissue\NameRecast\CamelCaseToSnakeCaseNameConverter;
use Flaphl\Element\Reissue\Normalizer\DateTimeNormalizer;
use Flaphl\Element\Reissue\Normalizer\MetadataAwareObjectNormalizer;
use Flaphl\Element\Reissue\Reissue;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

// Test fixtures
class ComplexUser
{
    #[SerializedName('user_id')]
    #[Groups(['public', 'admin'])]
    public int $id = 1;

    #[SerializedName('full_name')]
    #[Groups(['public'])]
    public string $name = 'John Doe';

    #[Groups(['admin'])]
    public string $email = 'john@example.com';

    #[Ignore]
    public string $password = 'secret';

    #[MaxDepth(2)]
    public ?ComplexUser $parent = null;

    #[Groups(['public'])]
    public \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime('2025-01-01 12:00:00');
    }
}

class UserWithMagicMethods
{
    public string $name = 'Test';
    public string $email = 'test@example.com';
    private bool $sleepCalled = false;

    public function __sleep(): array
    {
        $this->sleepCalled = true;
        return ['name']; // Only serialize name
    }

    public function wasSleepCalled(): bool
    {
        return $this->sleepCalled;
    }
}

/**
 * Comprehensive integration tests for the complete Reissue system.
 */
class CompleteIntegrationTest extends TestCase
{
    public function testFullSerializationWorkflow(): void
    {
        $user = new ComplexUser();
        
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(
                new CamelCaseToSnakeCaseNameConverter()
            ),
        ];

        $serializer = new Reissue($normalizers, $encoders);

        $json = $serializer->reissue($user, 'json');

        $this->assertIsString($json);
        $this->assertStringContainsString('"user_id":1', $json);
        $this->assertStringContainsString('"full_name":"John Doe"', $json);
        $this->assertStringNotContainsString('password', $json);
    }

    public function testSerializationWithGroups(): void
    {
        $user = new ComplexUser();
        
        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(),
        ];

        $serializer = new Reissue($normalizers, [new JsonEncoder()]);

        // Public group only
        $context = (new ReissueContextBuilder())->withGroups(['public'])->toArray();
        $json = $serializer->reissue($user, 'json', $context);

        $data = json_decode($json, true);
        
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('full_name', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayNotHasKey('email', $data); // Admin only
        $this->assertArrayNotHasKey('password', $data); // Ignored
    }

    public function testChainEncoderSupport(): void
    {
        $user = new ComplexUser();
        
        $chainEncoder = new ChainEncoder([
            new JsonEncoder(),
            new XmlEncoder(),
        ]);

        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(),
        ];

        $serializer = new Reissue($normalizers, [$chainEncoder]);

        // Test JSON
        $json = $serializer->reissue($user, 'json');
        $this->assertStringStartsWith('{', $json);

        // Test XML
        $xml = $serializer->reissue($user, 'xml');
        $this->assertStringStartsWith('<?xml', $xml);
    }

    public function testContextBuilders(): void
    {
        $user = new ComplexUser();
        
        $serializer = new Reissue(
            [new DateTimeNormalizer(), new MetadataAwareObjectNormalizer()],
            [new JsonEncoder()]
        );

        // Use JsonEncoderContextBuilder
        $context = (new JsonEncoderContextBuilder())
            ->withPrettyPrint()
            ->withUnescapedUnicode()
            ->toArray();

        $json = $serializer->reissue($user, 'json', $context);

        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString('    ', $json);
    }

    public function testXmlContextBuilder(): void
    {
        $user = new ComplexUser();
        
        $serializer = new Reissue(
            [new DateTimeNormalizer(), new MetadataAwareObjectNormalizer()],
            [new XmlEncoder()]
        );

        $context = (new XmlEncoderContextBuilder())
            ->withRootNodeName('user')
            ->withFormatOutput(true)
            ->toArray();

        $xml = $serializer->reissue($user, 'xml', $context);

        $this->assertStringContainsString('<user>', $xml);
    }

    public function testMagicMethodHandling(): void
    {
        $handler = new MagicProtectionHandler();
        $user = new UserWithMagicMethods();

        $this->assertTrue($handler->hasSleepMethod($user));
        
        $properties = $handler->invokeSleep($user);
        $this->assertEquals(['name'], $properties);
        $this->assertTrue($user->wasSleepCalled());
    }

    public function testDataCollectorTracking(): void
    {
        $collector = new ReissueDataCollector();
        
        $collector->collectReissue(['test'], 'json', 0.123, []);
        $collector->collectReissue(['test'], 'xml', 0.456, []);
        $collector->collectDeissue('User', 'json', 0.789, []);

        $summary = $collector->getSummary();

        $this->assertEquals(2, $summary['total_reissues']);
        $this->assertEquals(1, $summary['total_deissues']);
        $this->assertEquals(3, $summary['total_operations']);
        $this->assertGreaterThan(0, $summary['average_reissue_time']);
    }

    public function testCachedMetadataAtelier(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->method('set')->willReturnSelf();

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn($cacheItem);
        $cache->expects($this->once())->method('save');

        $atelier = new ClassMetadataAtelier(new AttributeLoader());
        $cachedAtelier = new CacheClassMetaDataAtelier($atelier, $cache);

        $metadata = $cachedAtelier->getMetadataFor(ComplexUser::class);
        
        $this->assertCount(6, $metadata->getAttributesMetadata());
    }

    public function testMaxDepthHandling(): void
    {
        $user1 = new ComplexUser();
        $user2 = new ComplexUser();
        $user3 = new ComplexUser();
        
        $user1->parent = $user2;
        $user2->parent = $user3;

        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(),
        ];

        $serializer = new Reissue($normalizers, [new JsonEncoder()]);
        $context = (new ReissueContextBuilder())->withMaxDepth(2)->toArray();

        $json = $serializer->reissue($user1, 'json', $context);
        
        $this->assertIsString($json);
        // Should not recurse infinitely
        $this->assertLessThan(10000, strlen($json));
    }

    public function testNameConverterIntegration(): void
    {
        $user = new ComplexUser();
        
        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(
                new CamelCaseToSnakeCaseNameConverter()
            ),
        ];

        $serializer = new Reissue($normalizers, [new JsonEncoder()]);
        $json = $serializer->reissue($user, 'json');

        $data = json_decode($json, true);
        
        // Created_at should be snake_case (from createdAt)
        $this->assertArrayHasKey('created_at', $data);
    }

    public function testCompleteDeserializationWorkflow(): void
    {
        $json = '{"user_id":42,"full_name":"Jane Doe","email":"jane@example.com","createdAt":"2025-01-01 12:00:00"}';

        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(),
        ];

        $serializer = new Reissue($normalizers, [new JsonEncoder()]);
        
        /** @var ComplexUser $user */
        $user = $serializer->deissue($json, ComplexUser::class, 'json');

        $this->assertInstanceOf(ComplexUser::class, $user);
        $this->assertEquals(42, $user->id);
        $this->assertEquals('Jane Doe', $user->name);
        $this->assertEquals('jane@example.com', $user->email);
        $this->assertInstanceOf(\DateTime::class, $user->createdAt);
    }

    public function testMultipleFormatSerialization(): void
    {
        $user = new ComplexUser();
        
        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(),
        ];

        $encoders = [
            new JsonEncoder(),
            new XmlEncoder(),
        ];

        $serializer = new Reissue($normalizers, $encoders);

        // Serialize to JSON
        $json = $serializer->reissue($user, 'json');
        $this->assertJson($json);

        // Serialize to XML
        $xml = $serializer->reissue($user, 'xml');
        $this->assertStringStartsWith('<?xml', $xml);

        // Both should contain the same data
        $this->assertStringContainsString('John Doe', $json);
        $this->assertStringContainsString('John Doe', $xml);
    }

    public function testStressTestWithLargeDataset(): void
    {
        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $user = new ComplexUser();
            $user->id = $i;
            $user->name = "User $i";
            $user->email = "user$i@example.com";
            $users[] = $user;
        }

        $normalizers = [
            new DateTimeNormalizer(),
            new MetadataAwareObjectNormalizer(),
        ];

        $serializer = new Reissue($normalizers, [new JsonEncoder()]);

        $startTime = microtime(true);
        $json = $serializer->reissue($users, 'json');
        $duration = microtime(true) - $startTime;

        $this->assertIsString($json);
        $this->assertLessThan(1.0, $duration); // Should complete in less than 1 second

        $decoded = json_decode($json, true);
        $this->assertCount(100, $decoded);
    }

    public function testPerformanceWithDataCollector(): void
    {
        $collector = new ReissueDataCollector();
        $operations = 1000;

        for ($i = 0; $i < $operations; $i++) {
            $collector->collectReissue(['data' => $i], 'json', 0.001 * rand(1, 100) / 100, []);
        }

        $summary = $collector->getSummary();
        
        $this->assertEquals($operations, $summary['total_reissues']);
        $this->assertGreaterThan(0, $summary['average_reissue_time']);

        $slowest = $collector->getSlowestOperations(10);
        $this->assertCount(10, $slowest);
        
        // Verify slowest are actually sorted
        for ($i = 0; $i < 9; $i++) {
            $this->assertGreaterThanOrEqual(
                $slowest[$i + 1]['duration'],
                $slowest[$i]['duration']
            );
        }
    }
}
