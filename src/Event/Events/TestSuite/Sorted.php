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
namespace Php_Unit\Event\Test_Suite;

use Php_Unit\Event\Event;
use Php_Unit\Event\Telemetry;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Sorted implements Event
{
    public function __construct(private Telemetry\Info $telemetry_info, private int $execution_order, private int $execution_order_defects, private bool $resolve_dependencies)
    {
    }
    public function telemetry_info(): Telemetry\Info
    {
        return $this->telemetry_info;
    }
    public function execution_order(): int
    {
        return $this->execution_order;
    }
    public function execution_order_defects(): int
    {
        return $this->execution_order_defects;
    }
    public function resolve_dependencies(): bool
    {
        return $this->resolve_dependencies;
    }
    /**
     * @return non-empty-string
     */
    public function as_string(): string
    {
        return 'Test Suite Sorted';
    }
}