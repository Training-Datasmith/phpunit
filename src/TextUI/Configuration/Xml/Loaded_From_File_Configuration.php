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
final readonly class Loaded_From_File_Configuration extends Configuration
{
    /**
     * @param non-empty-string $filename
     */
    public function __construct(private string $filename, private Validation_Result $validation_result, Extension_Bootstrap_Collection $extensions, Source $source, Code_Coverage $code_coverage, Groups $groups, Logging $logging, Php $php, Php_Unit $phpunit, Test_Suite_Collection $test_suite)
    {
        parent::__construct($extensions, $source, $code_coverage, $groups, $logging, $php, $phpunit, $test_suite);
    }
    /**
     * @return non-empty-string
     */
    public function filename(): string
    {
        return $this->filename;
    }
    public function has_validation_errors(): bool
    {
        return $this->validation_result->has_validation_errors();
    }
    public function validation_errors(): string
    {
        return $this->validation_result->as_string();
    }
    public function was_loaded_from_file(): bool
    {
        return true;
    }
}