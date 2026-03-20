<?php

declare (strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Php_Unit\Event\Telemetry;

use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Info
{
    public function __construct(private Snapshot $current, private Duration $duration_since_start, private Memory_Usage $memory_since_start, private Duration $duration_since_previous, private Memory_Usage $memory_since_previous)
    {
    }
    public function time(): Hr_Time
    {
        return $this->current->time();
    }
    public function memory_usage(): Memory_Usage
    {
        return $this->current->memory_usage();
    }
    public function peak_memory_usage(): Memory_Usage
    {
        return $this->current->peak_memory_usage();
    }
    public function duration_since_start(): Duration
    {
        return $this->duration_since_start;
    }
    public function memory_usage_since_start(): Memory_Usage
    {
        return $this->memory_since_start;
    }
    public function duration_since_previous(): Duration
    {
        return $this->duration_since_previous;
    }
    public function memory_usage_since_previous(): Memory_Usage
    {
        return $this->memory_since_previous;
    }
    public function garbage_collector_status(): Garbage_Collector_Status
    {
        return $this->current->garbage_collector_status();
    }
    public function as_string(): string
    {
        return sprintf('[%s / %s] [%d bytes]', $this->duration_since_start()->as_string(), $this->duration_since_previous()->as_string(), $this->peak_memory_usage()->bytes());
    }
}