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
use Php_Unit\Framework\Mock_Object\Runtime\Property_Hook;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface Stub
{
    public function method(Constraint|Property_Hook|string $constraint): Invocation_Stubber;
}