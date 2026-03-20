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
use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Loaded implements Event
{
    public function __construct(private Telemetry\Info $telemetry_info, private Test_Suite $test_suite)
    {
    }
    public function telemetry_info(): Telemetry\Info
    {
        return $this->telemetry_info;
    }
    public function test_suite(): Test_Suite
    {
        return $this->test_suite;
    }
    /**
     * @return non-empty-string
     */
    public function as_string(): string
    {
        return sprintf('Test Suite Loaded (%d test%s)', $this->test_suite->count(), $this->test_suite->count() !== 1 ? 's' : '');
    }
}