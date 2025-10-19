<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\DataCollector;

/**
 * Collects data about serialization/deserialization operations.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ReissueDataCollector
{
    private array $operations = [];
    private int $totalReissues = 0;
    private int $totalDeissues = 0;
    private float $totalReissueTime = 0.0;
    private float $totalDeissueTime = 0.0;

    /**
     * Records a reissue (serialization) operation.
     *
     * @param mixed $data The data being serialized
     * @param string $format The target format
     * @param float $duration Time taken in seconds
     * @param array $context The context used
     */
    public function collectReissue(mixed $data, string $format, float $duration, array $context = []): void
    {
        $this->totalReissues++;
        $this->totalReissueTime += $duration;

        $this->operations[] = [
            'type' => 'reissue',
            'data_type' => get_debug_type($data),
            'format' => $format,
            'duration' => $duration,
            'context' => $context,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Records a deissue (deserialization) operation.
     *
     * @param string $targetType The target type/class
     * @param string $format The source format
     * @param float $duration Time taken in seconds
     * @param array $context The context used
     */
    public function collectDeissue(string $targetType, string $format, float $duration, array $context = []): void
    {
        $this->totalDeissues++;
        $this->totalDeissueTime += $duration;

        $this->operations[] = [
            'type' => 'deissue',
            'target_type' => $targetType,
            'format' => $format,
            'duration' => $duration,
            'context' => $context,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Gets the total number of reissue operations.
     */
    public function getTotalReissues(): int
    {
        return $this->totalReissues;
    }

    /**
     * Gets the total number of deissue operations.
     */
    public function getTotalDeissues(): int
    {
        return $this->totalDeissues;
    }

    /**
     * Gets the total time spent on reissue operations.
     */
    public function getTotalReissueTime(): float
    {
        return $this->totalReissueTime;
    }

    /**
     * Gets the total time spent on deissue operations.
     */
    public function getTotalDeissueTime(): float
    {
        return $this->totalDeissueTime;
    }

    /**
     * Gets the average time for reissue operations.
     */
    public function getAverageReissueTime(): float
    {
        return $this->totalReissues > 0 ? $this->totalReissueTime / $this->totalReissues : 0.0;
    }

    /**
     * Gets the average time for deissue operations.
     */
    public function getAverageDeissueTime(): float
    {
        return $this->totalDeissues > 0 ? $this->totalDeissueTime / $this->totalDeissues : 0.0;
    }

    /**
     * Gets all recorded operations.
     *
     * @return array
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Gets operations by format.
     *
     * @param string $format The format to filter by
     *
     * @return array
     */
    public function getOperationsByFormat(string $format): array
    {
        return array_filter($this->operations, fn($op) => $op['format'] === $format);
    }

    /**
     * Gets the slowest operations.
     *
     * @param int $limit Number of operations to return
     *
     * @return array
     */
    public function getSlowestOperations(int $limit = 10): array
    {
        $operations = $this->operations;
        usort($operations, fn($a, $b) => $b['duration'] <=> $a['duration']);
        
        return array_slice($operations, 0, $limit);
    }

    /**
     * Resets all collected data.
     */
    public function reset(): void
    {
        $this->operations = [];
        $this->totalReissues = 0;
        $this->totalDeissues = 0;
        $this->totalReissueTime = 0.0;
        $this->totalDeissueTime = 0.0;
    }

    /**
     * Gets summary statistics.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'total_operations' => count($this->operations),
            'total_reissues' => $this->totalReissues,
            'total_deissues' => $this->totalDeissues,
            'total_reissue_time' => $this->totalReissueTime,
            'total_deissue_time' => $this->totalDeissueTime,
            'average_reissue_time' => $this->getAverageReissueTime(),
            'average_deissue_time' => $this->getAverageDeissueTime(),
        ];
    }
}
