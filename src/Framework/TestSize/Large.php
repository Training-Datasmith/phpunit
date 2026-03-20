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
namespace Php_Unit\Framework\Test_Size;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Large extends Known
{
    public function is_large(): true
    {
        return true;
    }
    public function is_greater_than(Test_Size $other): bool
    {
        return !$other->is_large();
    }
    public function as_string(): string
    {
        return 'large';
    }
}