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
namespace Php_Unit\Event\Runtime;

use const PHP_OS;
use const PHP_OS_FAMILY;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Operating_System
{
    private string $operating_system;
    private string $operating_system_family;
    public function __construct()
    {
        $this->operating_system = PHP_OS;
        $this->operating_system_family = PHP_OS_FAMILY;
    }
    public function operating_system(): string
    {
        return $this->operating_system;
    }
    public function operating_system_family(): string
    {
        return $this->operating_system_family;
    }
}