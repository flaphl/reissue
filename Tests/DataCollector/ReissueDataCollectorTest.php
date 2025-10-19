<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\DataCollector;

use Flaphl\Element\Reissue\DataCollector\ReissueDataCollector;
use PHPUnit\Framework\TestCase;

class ReissueDataCollectorTest extends TestCase
{
    private ReissueDataCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new ReissueDataCollector();
    }

    public function testCollectReissue(): void
    {
        $data = ['test' => 'data'];
        $this->collector->collectReissue($data, 'json', 0.123, []);

        $this->assertEquals(1, $this->collector->getTotalReissues());
    }

    public function testCollectDeissue(): void
    {
        $this->collector->collectDeissue('User', 'json', 0.456, []);

        $this->assertEquals(1, $this->collector->getTotalDeissues());
    }

    public function testGetTotalReissues(): void
    {
        $this->assertEquals(0, $this->collector->getTotalReissues());

        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->assertEquals(1, $this->collector->getTotalReissues());

        $this->collector->collectReissue([], 'xml', 0.2, []);
        $this->assertEquals(2, $this->collector->getTotalReissues());
    }

    public function testGetTotalDeissues(): void
    {
        $this->assertEquals(0, $this->collector->getTotalDeissues());

        $this->collector->collectDeissue('User', 'json', 0.1, []);
        $this->assertEquals(1, $this->collector->getTotalDeissues());

        $this->collector->collectDeissue('Product', 'xml', 0.2, []);
        $this->assertEquals(2, $this->collector->getTotalDeissues());
    }

    public function testGetTotalReissueTime(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'json', 0.2, []);
        $this->collector->collectReissue([], 'json', 0.3, []);

        $this->assertEqualsWithDelta(0.6, $this->collector->getTotalReissueTime(), 0.001);
    }

    public function testGetTotalDeissueTime(): void
    {
        $this->collector->collectDeissue('User', 'json', 0.1, []);
        $this->collector->collectDeissue('Product', 'json', 0.2, []);
        $this->collector->collectDeissue('Order', 'json', 0.4, []);

        $this->assertEqualsWithDelta(0.7, $this->collector->getTotalDeissueTime(), 0.001);
    }

    public function testGetAverageReissueTime(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'json', 0.2, []);
        $this->collector->collectReissue([], 'json', 0.3, []);

        $this->assertEqualsWithDelta(0.2, $this->collector->getAverageReissueTime(), 0.001);
    }

    public function testGetAverageDeissueTime(): void
    {
        $this->collector->collectDeissue('User', 'json', 0.1, []);
        $this->collector->collectDeissue('Product', 'json', 0.2, []);
        $this->collector->collectDeissue('Order', 'json', 0.3, []);

        $this->assertEqualsWithDelta(0.2, $this->collector->getAverageDeissueTime(), 0.001);
    }

    public function testGetAverageReissueTimeWithNoOperations(): void
    {
        $this->assertEquals(0.0, $this->collector->getAverageReissueTime());
    }

    public function testGetAverageDeissueTimeWithNoOperations(): void
    {
        $this->assertEquals(0.0, $this->collector->getAverageDeissueTime());
    }

    public function testGetOperations(): void
    {
        $this->collector->collectReissue(['test'], 'json', 0.1, ['key' => 'value']);
        $this->collector->collectDeissue('User', 'xml', 0.2, []);

        $operations = $this->collector->getOperations();

        $this->assertCount(2, $operations);
        $this->assertEquals('reissue', $operations[0]['type']);
        $this->assertEquals('deissue', $operations[1]['type']);
    }

    public function testGetOperationsByFormat(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'xml', 0.2, []);
        $this->collector->collectReissue([], 'json', 0.3, []);
        $this->collector->collectDeissue('User', 'json', 0.4, []);

        $jsonOps = $this->collector->getOperationsByFormat('json');
        $xmlOps = $this->collector->getOperationsByFormat('xml');

        $this->assertCount(3, $jsonOps);
        $this->assertCount(1, $xmlOps);
    }

    public function testGetSlowestOperations(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'json', 0.5, []);
        $this->collector->collectReissue([], 'json', 0.3, []);
        $this->collector->collectReissue([], 'json', 0.4, []);
        $this->collector->collectReissue([], 'json', 0.2, []);

        $slowest = $this->collector->getSlowestOperations(3);

        $this->assertCount(3, $slowest);
        $this->assertEquals(0.5, $slowest[0]['duration']);
        $this->assertEquals(0.4, $slowest[1]['duration']);
        $this->assertEquals(0.3, $slowest[2]['duration']);
    }

    public function testGetSlowestOperationsWithLimitGreaterThanTotal(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'json', 0.2, []);

        $slowest = $this->collector->getSlowestOperations(10);

        $this->assertCount(2, $slowest);
    }

    public function testGetSlowestOperationsWithZeroLimit(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'json', 0.2, []);

        $slowest = $this->collector->getSlowestOperations(0);

        $this->assertCount(0, $slowest);
    }

    public function testGetSummary(): void
    {
        $this->collector->collectReissue(['data'], 'json', 0.1, []);
        $this->collector->collectReissue(['data'], 'xml', 0.2, []);
        $this->collector->collectDeissue('User', 'json', 0.3, []);

        $summary = $this->collector->getSummary();

        $this->assertArrayHasKey('total_reissues', $summary);
        $this->assertArrayHasKey('total_deissues', $summary);
        $this->assertArrayHasKey('total_reissue_time', $summary);
        $this->assertArrayHasKey('total_deissue_time', $summary);
        $this->assertArrayHasKey('average_reissue_time', $summary);
        $this->assertArrayHasKey('average_deissue_time', $summary);
        $this->assertArrayHasKey('total_operations', $summary);

        $this->assertEquals(2, $summary['total_reissues']);
        $this->assertEquals(1, $summary['total_deissues']);
        $this->assertEquals(3, $summary['total_operations']);
    }

    public function testReset(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectDeissue('User', 'json', 0.2, []);

        $this->assertEquals(1, $this->collector->getTotalReissues());
        $this->assertEquals(1, $this->collector->getTotalDeissues());

        $this->collector->reset();

        $this->assertEquals(0, $this->collector->getTotalReissues());
        $this->assertEquals(0, $this->collector->getTotalDeissues());
        $this->assertEquals(0.0, $this->collector->getTotalReissueTime());
        $this->assertEquals(0.0, $this->collector->getTotalDeissueTime());
        $this->assertCount(0, $this->collector->getOperations());
    }

    public function testOperationStructure(): void
    {
        $data = ['key' => 'value'];
        $context = ['group' => 'public'];
        $this->collector->collectReissue($data, 'json', 0.123, $context);

        $operations = $this->collector->getOperations();
        $operation = $operations[0];

        $this->assertArrayHasKey('type', $operation);
        $this->assertArrayHasKey('data_type', $operation);
        $this->assertArrayHasKey('format', $operation);
        $this->assertArrayHasKey('duration', $operation);
        $this->assertArrayHasKey('context', $operation);
        $this->assertArrayHasKey('timestamp', $operation);

        $this->assertEquals('reissue', $operation['type']);
        $this->assertEquals('array', $operation['data_type']);
        $this->assertEquals('json', $operation['format']);
        $this->assertEquals(0.123, $operation['duration']);
        $this->assertEquals($context, $operation['context']);
    }

    public function testDeissueOperationStructure(): void
    {
        $context = ['key' => 'value'];
        $this->collector->collectDeissue('App\\Entity\\User', 'xml', 0.456, $context);

        $operations = $this->collector->getOperations();
        $operation = $operations[0];

        $this->assertEquals('deissue', $operation['type']);
        $this->assertEquals('App\\Entity\\User', $operation['target_type']);
        $this->assertEquals('xml', $operation['format']);
        $this->assertEquals(0.456, $operation['duration']);
    }

    public function testTimestampIsSet(): void
    {
        $before = microtime(true);
        $this->collector->collectReissue([], 'json', 0.1, []);
        $after = microtime(true);

        $operations = $this->collector->getOperations();
        $timestamp = $operations[0]['timestamp'];

        $this->assertGreaterThanOrEqual($before, $timestamp);
        $this->assertLessThanOrEqual($after, $timestamp);
    }

    public function testMultipleFormatsTracking(): void
    {
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue([], 'xml', 0.2, []);
        $this->collector->collectReissue([], 'csv', 0.3, []);

        $jsonOps = $this->collector->getOperationsByFormat('json');
        $xmlOps = $this->collector->getOperationsByFormat('xml');
        $csvOps = $this->collector->getOperationsByFormat('csv');

        $this->assertCount(1, $jsonOps);
        $this->assertCount(1, $xmlOps);
        $this->assertCount(1, $csvOps);
    }

    public function testDataTypeDetection(): void
    {
        $this->collector->collectReissue('string', 'json', 0.1, []);
        $this->collector->collectReissue(123, 'json', 0.1, []);
        $this->collector->collectReissue(12.34, 'json', 0.1, []);
        $this->collector->collectReissue(true, 'json', 0.1, []);
        $this->collector->collectReissue([], 'json', 0.1, []);
        $this->collector->collectReissue(new \stdClass(), 'json', 0.1, []);

        $operations = $this->collector->getOperations();

        $this->assertEquals('string', $operations[0]['data_type']);
        $this->assertContains($operations[1]['data_type'], ['int', 'integer']); // PHP 8+ uses 'int'
        $this->assertContains($operations[2]['data_type'], ['double', 'float']); // PHP 8.4+ uses 'float'
        $this->assertContains($operations[3]['data_type'], ['bool', 'boolean']); // PHP 8+ uses 'bool'
        $this->assertEquals('array', $operations[4]['data_type']);
        $this->assertEquals('stdClass', $operations[5]['data_type']);
    }

    public function testHighPrecisionTiming(): void
    {
        $this->collector->collectReissue([], 'json', 0.001234, []);
        
        $this->assertEquals(0.001234, $this->collector->getTotalReissueTime());
    }
}
