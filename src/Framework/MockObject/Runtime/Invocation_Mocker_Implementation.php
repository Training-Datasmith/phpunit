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

use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invocation_Mocker_Implementation extends Abstract_Invocation_Implementation implements Invocation_Mocker
{
    /**
     * @throws Exception
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     *
     * @return $this
     */
    public function with(mixed ...$arguments): Invocation_Mocker
    {
        $this->ensure_parameters_can_be_configured();
        $this->emit_deprecation_when_created_without_explicit_expects();
        $this->matcher->set_parameters_rule(new Rule\Parameters($arguments));
        return $this;
    }
    public function with_parameter_sets_in_order(mixed ...$arguments): Invocation_Mocker
    {
        $this->ensure_parameters_can_be_configured();
        $this->emit_deprecation_when_created_without_explicit_expects();
        $this->matcher->set_parameters_rule(new Rule\Ordered_Parameter_Sets($arguments));
        return $this;
    }
    public function with_parameter_sets_in_any_order(mixed ...$arguments): Invocation_Mocker
    {
        $this->ensure_parameters_can_be_configured();
        $this->emit_deprecation_when_created_without_explicit_expects();
        $this->matcher->set_parameters_rule(new Rule\Unordered_Parameter_Sets($arguments));
        return $this;
    }
    /**
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     *
     * @return $this
     */
    public function with_any_parameters(): Invocation_Mocker
    {
        $this->ensure_parameters_can_be_configured();
        $this->emit_deprecation_when_created_without_explicit_expects();
        $this->matcher->set_parameters_rule(new Rule\Any_Parameters());
        return $this;
    }
    /**
     * @param non-empty-string $id
     *
     * @throws MatcherAlreadyRegisteredException
     *
     * @return $this
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6537
     */
    public function id(string $id): Invocation_Mocker
    {
        $this->invocation_handler->register_matcher($id, $this->matcher);
        return $this;
    }
    /**
     * @param non-empty-string $id
     *
     * @return $this
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6537
     */
    public function after(string $id): Invocation_Mocker
    {
        $this->matcher->set_after_match_builder_id($id);
        return $this;
    }
    private function emit_deprecation_when_created_without_explicit_expects(): void
    {
        if (!$this->created_without_explicit_expects) {
            return;
        }
        Event_Facade::emitter()->test_triggered_phpunit_deprecation(null, 'Using with*() without expects() is deprecated and will no longer be possible in PHPUnit 14.');
    }
}