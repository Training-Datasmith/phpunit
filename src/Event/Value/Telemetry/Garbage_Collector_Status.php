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
final readonly class Garbage_Collector_Status
{
    public function __construct(private int $runs, private int $collected, private int $threshold, private int $roots, private float $application_time, private float $collector_time, private float $destructor_time, private float $free_time, private bool $running, private bool $protected, private bool $full, private int $buffer_size)
    {
    }
    public function runs(): int
    {
        return $this->runs;
    }
    public function collected(): int
    {
        return $this->collected;
    }
    public function threshold(): int
    {
        return $this->threshold;
    }
    public function roots(): int
    {
        return $this->roots;
    }
    public function application_time(): float
    {
        return $this->application_time;
    }
    public function collector_time(): float
    {
        return $this->collector_time;
    }
    public function destructor_time(): float
    {
        return $this->destructor_time;
    }
    public function free_time(): float
    {
        return $this->free_time;
    }
    public function is_running(): bool
    {
        return $this->running;
    }
    public function is_protected(): bool
    {
        return $this->protected;
    }
    public function is_full(): bool
    {
        return $this->full;
    }
    public function buffer_size(): int
    {
        return $this->buffer_size;
    }
}