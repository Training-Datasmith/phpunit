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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class System
{
    public function __construct(private Stop_Watch $stop_watch, private Memory_Meter $memory_meter, private Garbage_Collector_Status_Provider $garbage_collector_status_provider)
    {
    }
    public function snapshot(): Snapshot
    {
        return new Snapshot($this->stop_watch->current(), $this->memory_meter->memory_usage(), $this->memory_meter->peak_memory_usage(), $this->garbage_collector_status_provider->status());
    }
}