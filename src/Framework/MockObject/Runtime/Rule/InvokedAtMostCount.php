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

use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invoked_At_Most_Count extends Invocation_Order
{
    public function __construct(private readonly int $allowed_invocations)
    {
    }
    public function to_string(): string
    {
        if ($this->allowed_invocations === 1) {
            return 'invoked at most once';
        }
        return sprintf('invoked at most %d times', $this->allowed_invocations);
    }
    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        $actual_invocations = $this->number_of_invocations();
        if ($actual_invocations > $this->allowed_invocations) {
            throw new Expectation_Failed_Exception(sprintf('Expected invocation at most %d time%s but it occurred %d time%s.', $this->allowed_invocations, $this->allowed_invocations !== 1 ? 's' : '', $actual_invocations, $actual_invocations !== 1 ? 's' : ''));
        }
    }
    public function matches(Base_Invocation $invocation): bool
    {
        return true;
    }
}