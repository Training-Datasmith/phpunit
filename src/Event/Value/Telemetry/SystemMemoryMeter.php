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

use function memory_get_peak_usage;
use function memory_get_usage;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class System_Memory_Meter implements Memory_Meter
{
    public function memory_usage(): Memory_Usage
    {
        return Memory_Usage::from_bytes(memory_get_usage());
    }
    public function peak_memory_usage(): Memory_Usage
    {
        return Memory_Usage::from_bytes(memory_get_peak_usage());
    }
}