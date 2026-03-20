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

use function array_flip;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_pop;
use function assert;
use function count;
use function is_string;
use Php_Unit\Framework\Constraint\Constraint;
use Php_Unit\Framework\InvalidArgumentException;
use Php_Unit\Framework\Mock_Object\Runtime\Property_Hook;
use Php_Unit\Framework\Mock_Object\Stub\Consecutive_Calls;
use Php_Unit\Framework\Mock_Object\Stub\Exception;
use Php_Unit\Framework\Mock_Object\Stub\Return_Argument;
use Php_Unit\Framework\Mock_Object\Stub\Return_Callback;
use Php_Unit\Framework\Mock_Object\Stub\Return_Reference;
use Php_Unit\Framework\Mock_Object\Stub\Return_Self;
use Php_Unit\Framework\Mock_Object\Stub\Return_Stub;
use Php_Unit\Framework\Mock_Object\Stub\Return_Value_Map;
use Php_Unit\Framework\Mock_Object\Stub\Stub;
use function range;
use function strtolower;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Abstract_Invocation_Implementation implements Invocation_Stubber
{
    /**
     * @var list<ConfigurableMethod>
     */
    protected readonly array $configurable_methods;
    /**
     * @var ?array<string, int>
     */
    protected ?array $configurable_method_names = null;
    protected bool $created_without_explicit_expects = false;
    final public function __construct(protected readonly Invocation_Handler $invocation_handler, protected readonly Matcher $matcher, Configurable_Method ...$configurable_methods)
    {
        $this->configurable_methods = $configurable_methods;
    }
    /**
     * @param Constraint|non-empty-string|PropertyHook $constraint
     *
     * @throws InvalidArgumentException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     *
     * @return $this
     */
    final public function method(Constraint|Property_Hook|string $constraint): Invocation_Stubber
    {
        if ($this->matcher->has_method_name_rule()) {
            throw new Method_Name_Already_Configured_Exception();
        }
        if ($constraint instanceof Property_Hook) {
            $constraint = $constraint->as_string();
        }
        if (is_string($constraint)) {
            $this->configurable_method_names ??= array_flip(array_map(static fn(Configurable_Method $configurable): string => strtolower($configurable->name()), $this->configurable_methods));
            if (!array_key_exists(strtolower($constraint), $this->configurable_method_names)) {
                throw new Method_Cannot_Be_Configured_Exception($constraint);
            }
        }
        $this->matcher->set_method_name_rule(new Rule\Method_Name($constraint));
        return $this;
    }
    /**
     * @return $this
     */
    final public function will(Stub $stub): Invocation_Stubber
    {
        $this->matcher->set_stub($stub);
        return $this;
    }
    /**
     * @throws IncompatibleReturnValueException
     */
    final public function will_return(mixed $value, mixed ...$next_values): Invocation_Stubber
    {
        if (count($next_values) === 0) {
            $this->ensure_type_of_return_values([$value]);
            $stub = $value instanceof Stub ? $value : new Return_Stub($value);
            return $this->will($stub);
        }
        $values = array_merge([$value], $next_values);
        $this->ensure_type_of_return_values($values);
        $stub = new Consecutive_Calls($values);
        return $this->will($stub);
    }
    final public function will_return_reference(mixed &$reference): Invocation_Stubber
    {
        $stub = new Return_Reference($reference);
        return $this->will($stub);
    }
    final public function will_return_map(array $value_map): Invocation_Stubber
    {
        $method = $this->configured_method();
        assert($method instanceof Configurable_Method);
        $number_of_parameters = $method->number_of_parameters();
        $default_values = $method->default_parameter_values();
        $has_default_values = $default_values !== [];
        $_value_map = [];
        foreach ($value_map as $mapping) {
            $number_of_configured_parameters = count($mapping) - 1;
            if ($number_of_configured_parameters === $number_of_parameters || !$has_default_values) {
                $_value_map[] = $mapping;
                continue;
            }
            $_mapping = [];
            $return_value = array_pop($mapping);
            foreach (range(0, $number_of_parameters - 1) as $i) {
                if (array_key_exists($i, $mapping)) {
                    $_mapping[] = $mapping[$i];
                    continue;
                }
                if (array_key_exists($i, $default_values)) {
                    $_mapping[] = $default_values[$i];
                }
            }
            $_mapping[] = $return_value;
            $_value_map[] = $_mapping;
        }
        $stub = new Return_Value_Map($_value_map);
        return $this->will($stub);
    }
    final public function will_return_argument(int $argument_index): Invocation_Stubber
    {
        $stub = new Return_Argument($argument_index);
        return $this->will($stub);
    }
    final public function will_return_callback(callable $callback): Invocation_Stubber
    {
        $stub = new Return_Callback($callback);
        return $this->will($stub);
    }
    final public function will_return_self(): Invocation_Stubber
    {
        $stub = new Return_Self();
        return $this->will($stub);
    }
    final public function will_return_on_consecutive_calls(mixed ...$values): Invocation_Stubber
    {
        $stub = new Consecutive_Calls($values);
        return $this->will($stub);
    }
    final public function will_throw_exception(Throwable $exception): Invocation_Stubber
    {
        $stub = new Exception($exception);
        return $this->will($stub);
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function mark_as_created_without_explicit_expects(): static
    {
        $this->created_without_explicit_expects = true;
        return $this;
    }
    final public function seal(): void
    {
        $this->invocation_handler->seal($this->invocation_handler->is_mock_object());
    }
    /**
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     */
    final protected function ensure_parameters_can_be_configured(): void
    {
        if (!$this->matcher->has_method_name_rule()) {
            throw new Method_Name_Not_Configured_Exception();
        }
        if ($this->matcher->has_parameters_rule()) {
            throw new Method_Parameters_Already_Configured_Exception();
        }
    }
    private function configured_method(): ?Configurable_Method
    {
        $configured_method = null;
        foreach ($this->configurable_methods as $configurable_method) {
            if ($this->matcher->method_name_rule()->matches_name($configurable_method->name())) {
                if ($configured_method !== null) {
                    return null;
                }
                $configured_method = $configurable_method;
            }
        }
        return $configured_method;
    }
    /**
     * @param array<mixed> $values
     *
     * @throws IncompatibleReturnValueException
     */
    private function ensure_type_of_return_values(array $values): void
    {
        $configured_method = $this->configured_method();
        if ($configured_method === null) {
            return;
        }
        foreach ($values as $value) {
            if (!$configured_method->may_return($value)) {
                throw new Incompatible_Return_Value_Exception($configured_method, $value);
            }
        }
    }
}