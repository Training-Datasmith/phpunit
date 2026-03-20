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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use Php_Unit\Text_Ui\Configuration\Extension_Bootstrap_Collection;
use Php_Unit\Text_Ui\Configuration\Php;
use Php_Unit\Text_Ui\Configuration\Source;
use Php_Unit\Text_Ui\Configuration\Test_Suite_Collection;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Code_Coverage;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Logging;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
abstract readonly class Configuration
{
    public function __construct(private Extension_Bootstrap_Collection $extensions, private Source $source, private Code_Coverage $code_coverage, private Groups $groups, private Logging $logging, private Php $php, private Php_Unit $phpunit, private Test_Suite_Collection $test_suite)
    {
    }
    public function extensions(): Extension_Bootstrap_Collection
    {
        return $this->extensions;
    }
    public function source(): Source
    {
        return $this->source;
    }
    public function code_coverage(): Code_Coverage
    {
        return $this->code_coverage;
    }
    public function groups(): Groups
    {
        return $this->groups;
    }
    public function logging(): Logging
    {
        return $this->logging;
    }
    public function php(): Php
    {
        return $this->php;
    }
    public function phpunit(): Php_Unit
    {
        return $this->phpunit;
    }
    public function test_suite(): Test_Suite_Collection
    {
        return $this->test_suite;
    }
    /**
     * @phpstan-assert-if-true DefaultConfiguration $this
     */
    public function is_default(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true LoadedFromFileConfiguration $this
     */
    public function was_loaded_from_file(): bool
    {
        return false;
    }
}