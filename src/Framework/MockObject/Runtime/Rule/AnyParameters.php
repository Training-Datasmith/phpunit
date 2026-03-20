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
final class Any_Parameters implements Parameters_Rule
{
    /**
     * @throws void
     */
    public function apply(Base_Invocation $invocation): void
    {
    }
    public function verify(): void
    {
    }
}