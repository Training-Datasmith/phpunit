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

use function array_any;
use function array_unique;
use function array_values;
use Exception;
use function in_array;
use Php_Unit\Framework\Mock_Object\Rule\Invocation_Order;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_Count;
use Php_Unit\Framework\Mock_Object\Rule\Method_Name;
use function strtolower;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invocation_Handler
{
    /**
     * @var list<Matcher>
     */
    private array $matchers = [];
    /**
     * @var array<non-empty-string, Matcher>
     */
    private array $matcher_map = [];
    private bool $sealed = false;
    /**
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public function __construct(private readonly array $configurable_methods, private readonly bool $return_value_generation, private readonly bool $is_mock_object = false)
    {
    }
    public function is_mock_object(): bool
    {
        return $this->is_mock_object;
    }
    public function has_invocation_count_rule(): bool
    {
        return array_any($this->matchers, static fn(Matcher $matcher): bool => $matcher->has_invocation_count_rule());
    }
    public function has_parameters_rule(): bool
    {
        return array_any($this->matchers, static fn(Matcher $matcher): bool => $matcher->has_parameters_rule());
    }
    /**
     * Looks up the match builder with identification $id and returns it.
     *
     * @param non-empty-string $id
     */
    public function lookup_matcher(string $id): ?Matcher
    {
        return $this->matcher_map[$id] ?? null;
    }
    /**
     * Registers a matcher with the identification $id. The matcher can later be
     * looked up using lookupMatcher() to figure out if it has been invoked.
     *
     * @param non-empty-string $id
     *
     * @throws MatcherAlreadyRegisteredException
     */
    public function register_matcher(string $id, Matcher $matcher): void
    {
        if (isset($this->matcher_map[$id])) {
            throw new Matcher_Already_Registered_Exception($id);
        }
        $this->matcher_map[$id] = $matcher;
    }
    /**
     * @throws TestDoubleSealedException
     */
    public function expects(Invocation_Order $rule): Invocation_Mocker|Invocation_Stubber
    {
        if ($this->sealed) {
            throw new Test_Double_Sealed_Exception();
        }
        $matcher = new Matcher($rule);
        $this->add_matcher($matcher);
        if ($this->is_mock_object) {
            return new Invocation_Mocker_Implementation($this, $matcher, ...$this->configurable_methods);
        }
        return new Invocation_Stubber_Implementation($this, $matcher, ...$this->configurable_methods);
    }
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function invoke(Invocation $invocation): mixed
    {
        $exception = null;
        $has_return_value = false;
        $return_value = null;
        foreach ($this->matchers as $match) {
            try {
                if ($match->matches($invocation)) {
                    $value = $match->invoked($invocation);
                    if (!$has_return_value) {
                        $return_value = $value;
                        $has_return_value = true;
                    }
                }
            } catch (Exception $e) {
                $exception = $e;
            }
        }
        if ($exception !== null) {
            throw $exception;
        }
        if ($has_return_value) {
            return $return_value;
        }
        if (!$this->return_value_generation) {
            if (strtolower($invocation->method_name()) === '__tostring') {
                return '';
            }
            throw new Return_Value_Not_Configured_Exception($invocation);
        }
        return $invocation->generate_return_value();
    }
    /**
     * @throws Throwable
     */
    public function verify(): void
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
    public function seal(bool $is_mock_object): void
    {
        if ($this->sealed) {
            return;
        }
        $this->sealed = true;
        if (!$is_mock_object) {
            return;
        }
        $configured_methods = $this->configured_method_names();
        foreach ($this->configurable_methods as $method) {
            if (!in_array($method->name(), $configured_methods, true)) {
                $matcher = new Matcher(new Invoked_Count(0));
                $matcher->set_method_name_rule(new Method_Name($method->name()));
                $this->add_matcher($matcher);
            }
        }
    }
    public function is_sealed(): bool
    {
        return $this->sealed;
    }
    private function add_matcher(Matcher $matcher): void
    {
        $this->matchers[] = $matcher;
    }
    /**
     * Returns the list of method names that have been configured with expectations.
     * Only considers exact string matches for method names.
     * Methods with any() expectation are considered configured.
     *
     * @return list<non-empty-string>
     */
    private function configured_method_names(): array
    {
        $names = [];
        foreach ($this->matchers as $matcher) {
            if (!$matcher->has_method_name_rule()) {
                continue;
            }
            foreach ($this->configurable_methods as $method) {
                if ($matcher->method_name_rule()->matches_name($method->name())) {
                    $names[] = $method->name();
                }
            }
        }
        return array_values(array_unique($names));
    }
}