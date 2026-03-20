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

interface Invocation_Mocker extends Invocation_Stubber
{
    /**
     * @return $this
     */
    public function with(mixed ...$arguments): self;
    /**
     * @return $this
     */
    public function with_parameter_sets_in_order(mixed ...$arguments): self;
    /**
     * @return $this
     */
    public function with_parameter_sets_in_any_order(mixed ...$arguments): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function with_any_parameters(): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param non-empty-string $id
     *
     * @return $this
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6537
     */
    public function id(string $id): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param non-empty-string $id
     *
     * @return $this
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6537
     */
    public function after(string $id): self;
}