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

use function assert;
use function count;
use Exception;
use Php_Unit\Framework\Constraint\Callback;
use Php_Unit\Framework\Constraint\Constraint;
use Php_Unit\Framework\Constraint\Is_Anything;
use Php_Unit\Framework\Constraint\Is_Equal;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
use Php_Unit\Util\Test;
use Reflection_Exception;
use ReflectionMethod;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Parameters implements Parameters_Rule
{
    /**
     * @var list<Constraint>
     */
    private array $parameters = [];
    private ?Base_Invocation $invocation = null;
    private null|bool|Expectation_Failed_Exception $parameter_verification_result = null;
    private bool $use_assertion_count = true;
    /**
     * @param array<mixed> $parameters
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof Constraint) {
                $parameter = new Is_Equal($parameter);
            }
            $this->parameters[] = $parameter;
        }
    }
    /**
     * @throws Exception
     */
    public function apply(Base_Invocation $invocation): void
    {
        $this->invocation = $invocation;
        $this->parameter_verification_result = null;
        try {
            $this->parameter_verification_result = $this->do_verify();
        } catch (Expectation_Failed_Exception $e) {
            $this->parameter_verification_result = $e;
            throw $this->parameter_verification_result;
        }
    }
    /**
     * Checks if the invocation $invocation matches the current rules. If it
     * does the rule will get the invoked() method called which should check
     * if an expectation is met.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        $this->do_verify();
    }
    public function use_assertion_count(bool $use_assertion_count): void
    {
        $this->use_assertion_count = $use_assertion_count;
    }
    /**
     * @throws ExpectationFailedException
     */
    private function do_verify(): bool
    {
        if (isset($this->parameter_verification_result)) {
            return $this->guard_against_duplicate_evaluation_of_parameter_constraints();
        }
        if ($this->invocation === null) {
            throw new Expectation_Failed_Exception('Doubled method does not exist.');
        }
        if (count($this->invocation->parameters()) < count($this->parameters)) {
            $message = 'Parameter count for invocation %s is too low.';
            // The user called `->with($this->anything())`, but may have meant
            // `->withAnyParameters()`.
            //
            // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/199
            if (count($this->parameters) === 1 && $this->parameters[0]::class === Is_Anything::class) {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }
            $this->increment_assertion_count();
            throw new Expectation_Failed_Exception(sprintf($message, $this->invocation->to_string()));
        }
        $parameters = $this->parameters($this->invocation);
        foreach ($this->parameters as $i => $parameter) {
            if ($parameter instanceof Callback && $parameter->is_variadic()) {
                $other = $this->invocation->parameters();
            } else {
                $other = $this->invocation->parameters()[$i];
            }
            $this->increment_assertion_count();
            $parameter->evaluate($other, sprintf('Parameter %s for invocation %s does not match expected value.', $parameters[$i] ?? (string) $i, $this->invocation->to_string()));
        }
        return true;
    }
    /**
     * @throws ExpectationFailedException
     */
    private function guard_against_duplicate_evaluation_of_parameter_constraints(): bool
    {
        if ($this->parameter_verification_result instanceof Expectation_Failed_Exception) {
            throw $this->parameter_verification_result;
        }
        return (bool) $this->parameter_verification_result;
    }
    private function increment_assertion_count(): void
    {
        if ($this->use_assertion_count === false) {
            return;
        }
        Test::current_test_case()->add_to_assertion_count(1);
    }
    /**
     * @return array<non-negative-int, non-empty-string>
     */
    private function parameters(Base_Invocation $invocation): array
    {
        $parameters = [];
        try {
            $reflector = new ReflectionMethod($invocation->class_name(), $invocation->method_name());
            foreach ($reflector->get_parameters() as $parameter) {
                assert($parameter->get_position() >= 0);
                $parameters[$parameter->get_position()] = '$' . $parameter->get_name();
            }
        } catch (Reflection_Exception) {
        }
        return $parameters;
    }
}