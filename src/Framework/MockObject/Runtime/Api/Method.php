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

use Php_Unit\Framework\Constraint\Constraint;
use Php_Unit\Framework\Mock_Object\Rule\Any_Invoked_Count;
use Php_Unit\Framework\Mock_Object\Runtime\Property_Hook;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait Method
{
    abstract public function __phpunit_get_invocation_handler(): Invocation_Handler;
    public function method(Constraint|Property_Hook|string $constraint): Invocation_Stubber
    {
        return $this->__phpunit_get_invocation_handler()->expects(new Any_Invoked_Count())->method($constraint)->mark_as_created_without_explicit_expects();
    }
}