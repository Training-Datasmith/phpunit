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

use Php_Unit\Framework\Constraint\Constraint;
use Php_Unit\Framework\Mock_Object\Runtime\Property_Hook;
use Php_Unit\Framework\Mock_Object\Stub\Stub;
use Throwable;
interface Invocation_Stubber
{
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param Constraint|non-empty-string|PropertyHook $constraint
     *
     * @return $this
     */
    public function method(Constraint|Property_Hook|string $constraint): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will(Stub $stub): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_return(mixed $value, mixed ...$next_values): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_return_reference(mixed &$reference): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param array<int, array<int, mixed>> $valueMap
     *
     * @return $this
     */
    public function will_return_map(array $value_map): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_return_argument(int $argument_index): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_return_callback(callable $callback): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_return_self(): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_return_on_consecutive_calls(mixed ...$values): self;
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will_throw_exception(Throwable $exception): self;
    public function seal(): void;
}