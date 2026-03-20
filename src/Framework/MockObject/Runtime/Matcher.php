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

use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Mock_Object\Rule\Any_Invoked_Count;
use Php_Unit\Framework\Mock_Object\Rule\Any_Parameters;
use Php_Unit\Framework\Mock_Object\Rule\Invocation_Order;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Most_Count;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_Count;
use Php_Unit\Framework\Mock_Object\Rule\Method_Name;
use Php_Unit\Framework\Mock_Object\Rule\Parameters_Rule;
use Php_Unit\Framework\Mock_Object\Stub\Stub;
use Php_Unit\Util\Throwable_To_String_Mapper;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Matcher
{
    /**
     * @var ?non-empty-string
     */
    private ?string $after_match_builder_id = null;
    private ?Method_Name $method_name_rule = null;
    private ?Parameters_Rule $parameters_rule = null;
    private ?Stub $stub = null;
    public function __construct(private readonly Invocation_Order $invocation_rule)
    {
    }
    public function has_invocation_count_rule(): bool
    {
        return !$this->invocation_rule instanceof Any_Invoked_Count;
    }
    /**
     * @phpstan-assert-if-true !null $this->methodNameRule
     */
    public function has_method_name_rule(): bool
    {
        return $this->method_name_rule !== null;
    }
    /**
     * @throws MethodNameNotConfiguredException
     */
    public function method_name_rule(): Method_Name
    {
        if (!$this->has_method_name_rule()) {
            throw new Method_Name_Not_Configured_Exception();
        }
        return $this->method_name_rule;
    }
    public function set_method_name_rule(Method_Name $rule): void
    {
        $this->method_name_rule = $rule;
    }
    /**
     * @phpstan-assert-if-true !null $this->parametersRule
     */
    public function has_parameters_rule(): bool
    {
        return $this->parameters_rule !== null;
    }
    public function set_parameters_rule(Parameters_Rule $rule): void
    {
        $this->parameters_rule = $rule;
    }
    public function set_stub(Stub $stub): void
    {
        $this->stub = $stub;
    }
    /**
     * @param non-empty-string $id
     */
    public function set_after_match_builder_id(string $id): void
    {
        $this->after_match_builder_id = $id;
    }
    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws MatchBuilderNotFoundException
     * @throws MethodNameNotConfiguredException
     * @throws RuntimeException
     */
    public function invoked(Invocation $invocation): mixed
    {
        if ($this->method_name_rule === null) {
            throw new Method_Name_Not_Configured_Exception();
        }
        if ($this->after_match_builder_id !== null) {
            $matcher = $invocation->object()->__phpunit_get_invocation_handler()->lookup_matcher($this->after_match_builder_id);
            if ($matcher === null) {
                throw new Match_Builder_Not_Found_Exception($this->after_match_builder_id);
            }
        }
        $this->invocation_rule->invoked($invocation);
        try {
            $this->parameters_rule?->apply($invocation);
        } catch (Expectation_Failed_Exception $e) {
            throw new Expectation_Failed_Exception(sprintf("Expectation for %s failed.\n%s", $this->method_name_rule->failure_description(), $e->get_message()), $e->get_comparison_failure());
        }
        if ($this->stub !== null) {
            return $this->stub->invoke($invocation);
        }
        return $invocation->generate_return_value();
    }
    /**
     * @throws ExpectationFailedException
     * @throws MatchBuilderNotFoundException
     * @throws MethodNameNotConfiguredException
     * @throws RuntimeException
     */
    public function matches(Invocation $invocation): bool
    {
        if ($this->after_match_builder_id !== null) {
            $matcher = $invocation->object()->__phpunit_get_invocation_handler()->lookup_matcher($this->after_match_builder_id);
            if ($matcher === null) {
                throw new Match_Builder_Not_Found_Exception($this->after_match_builder_id);
            }
            if (!$matcher->invocation_rule->has_been_invoked()) {
                return false;
            }
        }
        if ($this->method_name_rule === null) {
            throw new Method_Name_Not_Configured_Exception();
        }
        if (!$this->invocation_rule->matches($invocation)) {
            return false;
        }
        try {
            if (!$this->method_name_rule->matches($invocation)) {
                return false;
            }
        } catch (Expectation_Failed_Exception $e) {
            throw new Expectation_Failed_Exception(sprintf("Expectation for %s failed.\n%s", $this->method_name_rule->failure_description(), $e->get_message()), $e->get_comparison_failure());
        }
        return true;
    }
    /**
     * @throws ExpectationFailedException
     * @throws MethodNameNotConfiguredException
     */
    public function verify(): void
    {
        if ($this->method_name_rule === null) {
            throw new Method_Name_Not_Configured_Exception();
        }
        try {
            $this->invocation_rule->verify();
        } catch (Expectation_Failed_Exception) {
            $actual = $this->invocation_rule->number_of_invocations();
            if ($actual === 0) {
                $invoked = 'never invoked';
            } elseif ($actual === 1) {
                $invoked = 'invoked once';
            } else {
                $invoked = sprintf('invoked %d times', $actual);
            }
            throw new Expectation_Failed_Exception(sprintf('%s was expected to be %s but was %s.', $this->method_name_rule->failure_description(), $this->invocation_rule->to_string(), $invoked));
        }
        if ($this->parameters_rule === null) {
            $this->parameters_rule = new Any_Parameters();
        }
        $invocation_is_any = $this->invocation_rule instanceof Any_Invoked_Count;
        $invocation_is_never = $this->invocation_rule instanceof Invoked_Count && $this->invocation_rule->is_never();
        $invocation_is_at_most = $this->invocation_rule instanceof Invoked_At_Most_Count;
        if (!$invocation_is_any && !$invocation_is_never && !$invocation_is_at_most) {
            try {
                $this->parameters_rule->verify();
            } catch (Expectation_Failed_Exception $e) {
                throw new Expectation_Failed_Exception(sprintf("Expectation for %s failed.\n%s", $this->method_name_rule->failure_description(), Throwable_To_String_Mapper::map($e)));
            }
        }
    }
}