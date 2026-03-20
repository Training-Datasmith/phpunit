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
final class Invoked_At_Least_Count extends Invocation_Order
{
    public function __construct(private readonly int $required_invocations)
    {
    }
    public function to_string(): string
    {
        return sprintf('invoked at least %d time%s', $this->required_invocations, $this->required_invocations !== 1 ? 's' : '');
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
        if ($actual_invocations < $this->required_invocations) {
            throw new Expectation_Failed_Exception(sprintf('Expected invocation at least %d time%s but it occurred %d time%s.', $this->required_invocations, $this->required_invocations !== 1 ? 's' : '', $actual_invocations, $actual_invocations !== 1 ? 's' : ''));
        }
    }
    public function matches(Base_Invocation $invocation): bool
    {
        return true;
    }
}