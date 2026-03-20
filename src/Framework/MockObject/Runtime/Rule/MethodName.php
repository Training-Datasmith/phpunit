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

use function is_string;
use Php_Unit\Framework\Constraint\Constraint;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\InvalidArgumentException;
use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
use Php_Unit\Framework\Mock_Object\Method_Name_Constraint;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Method_Name
{
    private Constraint $constraint;
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Constraint|string $constraint)
    {
        if (is_string($constraint)) {
            $constraint = new Method_Name_Constraint($constraint);
        }
        $this->constraint = $constraint;
    }
    public function to_string(): string
    {
        return 'method name ' . $this->constraint->to_string();
    }
    public function failure_description(): string
    {
        if ($this->constraint instanceof Method_Name_Constraint) {
            return '"' . $this->constraint->method_name() . '()"';
        }
        return $this->to_string();
    }
    /**
     * @throws ExpectationFailedException
     */
    public function matches(Base_Invocation $invocation): bool
    {
        return $this->matches_name($invocation->method_name());
    }
    /**
     * @throws ExpectationFailedException
     */
    public function matches_name(string $method_name): bool
    {
        return (bool) $this->constraint->evaluate($method_name, '', true);
    }
}