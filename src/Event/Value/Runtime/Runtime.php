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

use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Runtime
{
    private Operating_System $operating_system;
    private PHP $php;
    private Php_Unit $phpunit;
    public function __construct()
    {
        $this->operating_system = new Operating_System();
        $this->php = new PHP();
        $this->phpunit = new Php_Unit();
    }
    public function as_string(): string
    {
        $php = $this->php();
        return sprintf('PHPUnit %s using PHP %s (%s) on %s', $this->phpunit()->version_id(), $php->version(), $php->sapi(), $this->operating_system()->operating_system());
    }
    public function operating_system(): Operating_System
    {
        return $this->operating_system;
    }
    public function php(): PHP
    {
        return $this->php;
    }
    public function phpunit(): Php_Unit
    {
        return $this->phpunit;
    }
}