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
final class Invoked_Count extends Invocation_Order
{
    public function __construct(private readonly int $expected_count)
    {
    }
    public function is_never(): bool
    {
        return $this->expected_count === 0;
    }
    public function to_string(): string
    {
        if ($this->expected_count === 1) {
            return 'invoked once';
        }
        return sprintf('invoked %d times', $this->expected_count);
    }
    public function matches(Base_Invocation $invocation): bool
    {
        return true;
    }
    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        $actual_count = $this->number_of_invocations();
        if ($actual_count !== $this->expected_count) {
            throw new Expectation_Failed_Exception(sprintf('Method was expected to be called %d time%s, actually called %d time%s.', $this->expected_count, $this->expected_count !== 1 ? 's' : '', $actual_count, $actual_count !== 1 ? 's' : ''));
        }
    }
    /**
     * @throws ExpectationFailedException
     */
    protected function invoked_do(Base_Invocation $invocation): void
    {
        $count = $this->number_of_invocations();
        if ($count > $this->expected_count) {
            $message = $invocation->to_string() . ' ';
            $message .= match ($this->expected_count) {
                0 => 'was not expected to be called.',
                1 => 'was not expected to be called more than once.',
                default => sprintf('was not expected to be called more than %d times.', $this->expected_count),
            };
            throw new Expectation_Failed_Exception($message);
        }
    }
}