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
namespace Php_Unit\Event\Test_Runner;

use Php_Unit\Event\Event;
use Php_Unit\Event\Telemetry;
use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Extension_Bootstrapped implements Event
{
    /**
     * @param class-string          $className
     * @param array<string, string> $parameters
     */
    public function __construct(private Telemetry\Info $telemetry_info, private string $class_name, private array $parameters)
    {
    }
    public function telemetry_info(): Telemetry\Info
    {
        return $this->telemetry_info;
    }
    /**
     * @return class-string
     */
    public function class_name(): string
    {
        return $this->class_name;
    }
    /**
     * @return array<string, string>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
    /**
     * @return non-empty-string
     */
    public function as_string(): string
    {
        return sprintf('Extension Bootstrapped (%s)', $this->class_name);
    }
}