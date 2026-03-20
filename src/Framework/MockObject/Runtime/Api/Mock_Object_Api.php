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

use Php_Unit\Framework\Mock_Object\Rule\Invocation_Order;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait Mock_Object_Api
{
    public function __phpunit_has_invocation_count_rule(): bool
    {
        return $this->__phpunit_get_invocation_handler()->has_invocation_count_rule();
    }
    public function __phpunit_has_parameters_rule(): bool
    {
        return $this->__phpunit_get_invocation_handler()->has_parameters_rule();
    }
    public function __phpunit_verify(bool $unset_invocation_mocker = true): void
    {
        $this->__phpunit_get_invocation_handler()->verify();
        if ($unset_invocation_mocker) {
            $this->__phpunit_unset_invocation_mocker();
        }
    }
    abstract public function __phpunit_state(): Test_Double_State;
    abstract public function __phpunit_get_invocation_handler(): Invocation_Handler;
    abstract public function __phpunit_unset_invocation_mocker(): void;
    public function expects(Invocation_Order $matcher): Invocation_Mocker
    {
        return $this->__phpunit_get_invocation_handler()->expects($matcher);
    }
}