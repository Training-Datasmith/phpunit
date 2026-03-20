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

use function count;
use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
use Php_Unit\Framework\Self_Describing;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Invocation_Order implements Self_Describing
{
    /**
     * @var list<BaseInvocation>
     */
    private array $invocations = [];
    public function number_of_invocations(): int
    {
        return count($this->invocations);
    }
    public function has_been_invoked(): bool
    {
        return count($this->invocations) > 0;
    }
    final public function invoked(Base_Invocation $invocation): void
    {
        $this->invocations[] = $invocation;
        $this->invoked_do($invocation);
    }
    abstract public function matches(Base_Invocation $invocation): bool;
    abstract public function verify(): void;
    protected function invoked_do(Base_Invocation $invocation): void
    {
    }
}