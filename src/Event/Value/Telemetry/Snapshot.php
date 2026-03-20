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

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Snapshot
{
    public function __construct(private Hr_Time $time, private Memory_Usage $memory_usage, private Memory_Usage $peak_memory_usage, private Garbage_Collector_Status $garbage_collector_status)
    {
    }
    public function time(): Hr_Time
    {
        return $this->time;
    }
    public function memory_usage(): Memory_Usage
    {
        return $this->memory_usage;
    }
    public function peak_memory_usage(): Memory_Usage
    {
        return $this->peak_memory_usage;
    }
    public function garbage_collector_status(): Garbage_Collector_Status
    {
        return $this->garbage_collector_status;
    }
}