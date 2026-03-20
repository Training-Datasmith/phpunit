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
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Mock_Object_Internal extends Mock_Object, Stub_Internal
{
    public function __phpunit_has_invocation_count_rule(): bool;
    public function __phpunit_has_parameters_rule(): bool;
    public function __phpunit_verify(bool $unset_invocation_mocker = true): void;
}