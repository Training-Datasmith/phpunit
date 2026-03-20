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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait Stub_Api
{
    private readonly Test_Double_State $__phpunit_state;
    public function __phpunit_state(): Test_Double_State
    {
        return $this->__phpunit_state ?? new Test_Double_State([], true, false);
    }
    public function __phpunit_get_invocation_handler(): Invocation_Handler
    {
        return $this->__phpunit_state()->invocation_handler();
    }
    public function __phpunit_unset_invocation_mocker(): void
    {
        $this->__phpunit_state()->unset_invocation_handler();
    }
}