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
namespace Php_Unit\Framework\Mock_Object\Rule;

use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Any_Invoked_Count extends Invocation_Order
{
    public function to_string(): string
    {
        return 'invoked zero or more times';
    }
    public function verify(): void
    {
    }
    public function matches(Base_Invocation $invocation): bool
    {
        return true;
    }
}