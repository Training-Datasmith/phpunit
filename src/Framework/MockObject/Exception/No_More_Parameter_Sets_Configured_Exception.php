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
namespace Php_Unit\Framework\Mock_Object;

use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class No_More_Parameter_Sets_Configured_Exception extends \Php_Unit\Framework\Exception implements Exception
{
    public function __construct(Invocation $invocation, int $number_of_configured_parameter_sets)
    {
        parent::__construct(sprintf('Not enough parameter sets configured, only %d parameter sets given for %s::%s()', $number_of_configured_parameter_sets, $invocation->class_name(), $invocation->method_name()));
    }
}