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
namespace Php_Unit\Framework;

use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Comparison_Method_Does_Not_Declare_Exactly_One_Parameter_Exception extends Exception
{
    public function __construct(string $class_name, string $method_name)
    {
        parent::__construct(sprintf('Comparison method %s::%s() does not declare exactly one parameter.', $class_name, $method_name));
    }
}