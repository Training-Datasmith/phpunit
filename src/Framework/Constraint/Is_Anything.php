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
final class Is_Anything extends Constraint
{
    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws void
     */
    public function evaluate(mixed $other, string $description = '', bool $return_result = false): ?bool
    {
        return $return_result ? true : null;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return 'is anything';
    }
    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return 0;
    }
}