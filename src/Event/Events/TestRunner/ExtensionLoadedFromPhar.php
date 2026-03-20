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
final readonly class Extension_Loaded_From_Phar implements Event
{
    /**
     * @param non-empty-string $filename
     * @param non-empty-string $name
     * @param non-empty-string $version
     */
    public function __construct(private Telemetry\Info $telemetry_info, private string $filename, private string $name, private string $version)
    {
    }
    public function telemetry_info(): Telemetry\Info
    {
        return $this->telemetry_info;
    }
    /**
     * @return non-empty-string
     */
    public function filename(): string
    {
        return $this->filename;
    }
    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
    /**
     * @return non-empty-string
     */
    public function version(): string
    {
        return $this->version;
    }
    /**
     * @return non-empty-string
     */
    public function as_string(): string
    {
        return sprintf('Extension Loaded from PHAR (%s %s)', $this->name, $this->version);
    }
}