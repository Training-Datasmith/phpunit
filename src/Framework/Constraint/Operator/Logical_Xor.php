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

use function array_reduce;
use function array_shift;
use function assert;
use function is_bool;
use Php_Unit\Framework\Expectation_Failed_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Logical_Xor extends Binary_Operator
{
    public static function from_constraints(mixed ...$constraints): self
    {
        return new self(...$constraints);
    }
    /**
     * Returns the name of this operator.
     */
    public function operator(): string
    {
        return 'xor';
    }
    /**
     * Returns this operator's precedence.
     *
     * @see https://www.php.net/manual/en/language.operators.precedence.php.
     */
    public function precedence(): int
    {
        return 23;
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws ExpectationFailedException
     */
    public function matches(mixed $other): bool
    {
        $constraints = $this->constraints();
        $initial = array_shift($constraints);
        if ($initial === null) {
            return false;
        }
        $result = array_reduce($constraints, static fn(?bool $matches, Constraint $constraint): bool => $matches xor $constraint->evaluate($other, '', true), $initial->evaluate($other, '', true));
        assert(is_bool($result));
        return $result;
    }
}