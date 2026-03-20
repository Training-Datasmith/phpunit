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
namespace Php_Unit\Event\Application;

use Php_Unit\Event\Event;
use Php_Unit\Event\Runtime\Runtime;
use Php_Unit\Event\Telemetry;
use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Started implements Event
{
    public function __construct(private Telemetry\Info $telemetry_info, private Runtime $runtime)
    {
    }
    public function telemetry_info(): Telemetry\Info
    {
        return $this->telemetry_info;
    }
    public function runtime(): Runtime
    {
        return $this->runtime;
    }
    /**
     * @return non-empty-string
     */
    public function as_string(): string
    {
        return sprintf('PHPUnit Started (%s)', $this->runtime->as_string());
    }
}