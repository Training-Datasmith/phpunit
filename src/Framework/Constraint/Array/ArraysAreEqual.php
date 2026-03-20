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
namespace Php_Unit\Framework\Constraint;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Arrays_Are_Equal extends Array_Comparison
{
    protected function compare_arrays(mixed $expected, mixed $actual): bool
    {
        /** @phpstan-ignore equal.notAllowed */
        return $expected == $actual;
    }
    /**
     * @return 'equal'
     */
    protected function comparison_type(): string
    {
        return 'equal';
    }
}