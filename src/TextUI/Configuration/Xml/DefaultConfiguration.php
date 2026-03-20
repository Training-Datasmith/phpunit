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

use Php_Unit\Runner\Test_Suite_Sorter;
use Php_Unit\Text_Ui\Configuration\Constant_Collection;
use Php_Unit\Text_Ui\Configuration\Directory_Collection;
use Php_Unit\Text_Ui\Configuration\Extension_Bootstrap_Collection;
use Php_Unit\Text_Ui\Configuration\Filter_Directory_Collection;
use Php_Unit\Text_Ui\Configuration\Filter_File_Collection;
use Php_Unit\Text_Ui\Configuration\Group_Collection;
use Php_Unit\Text_Ui\Configuration\Ini_Setting_Collection;
use Php_Unit\Text_Ui\Configuration\Php;
use Php_Unit\Text_Ui\Configuration\Source;
use Php_Unit\Text_Ui\Configuration\Test_Suite_Collection;
use Php_Unit\Text_Ui\Configuration\Variable_Collection;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Code_Coverage;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Logging;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Default_Configuration extends Configuration
{
    public static function create(): self
    {
        return new self(Extension_Bootstrap_Collection::from_array([]), new Source(null, false, Filter_Directory_Collection::from_array([]), Filter_File_Collection::from_array([]), Filter_Directory_Collection::from_array([]), Filter_File_Collection::from_array([]), false, false, false, false, false, false, false, false, false, ['functions' => [], 'methods' => []], false, false, false, true), new Code_Coverage(false, true, false, false, null, null, null, null, null, null, null, null), new Groups(Group_Collection::from_array([]), Group_Collection::from_array([])), new Logging(null, null, null, null, null), new Php(Directory_Collection::from_array([]), Ini_Setting_Collection::from_array([]), Constant_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([]), Variable_Collection::from_array([])), new Php_Unit(null, true, 80, \Php_Unit\Text_Ui\Configuration\Configuration::COLOR_DEFAULT, false, false, false, false, false, false, false, false, false, false, false, false, false, null, [], false, false, false, false, false, true, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, null, false, false, true, false, false, 1, 1, 10, 60, null, Test_Suite_Sorter::ORDER_DEFAULT, true, false, false, false, false, false, false, 100, 10), Test_Suite_Collection::from_array([]));
    }
    public function is_default(): bool
    {
        return true;
    }
}