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

use function array_shift;
use function count;
use function is_array;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
use Php_Unit\Framework\Mock_Object\No_More_Parameter_Sets_Configured_Exception;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Ordered_Parameter_Sets implements Parameters_Rule
{
    /**
     * @var list<Parameters>
     */
    private array $stack = [];
    /**
     * @var list<Parameters>
     */
    private array $applied = [];
    private readonly int $number_of_configured_parameter_sets;
    /**
     * @param list<Parameters> $stack
     */
    public function __construct(array $stack)
    {
        foreach ($stack as $parameters) {
            $this->stack[] = new Parameters(is_array($parameters) ? $parameters : [$parameters]);
        }
        $this->number_of_configured_parameter_sets = count($stack);
    }
    public function apply(Base_Invocation $invocation): void
    {
        if ($this->stack === []) {
            throw new No_More_Parameter_Sets_Configured_Exception($invocation, $this->number_of_configured_parameter_sets);
        }
        $parameters = array_shift($this->stack);
        $this->applied[] = $parameters;
        $parameters->apply($invocation);
    }
    /**
     * Checks if the invocation $invocation matches the current rules. If it
     * does the rule will get the invoked() method called which should check
     * if an expectation is met.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        if (count($this->applied) !== $this->number_of_configured_parameter_sets && count($this->stack) > 0) {
            throw new Expectation_Failed_Exception(sprintf('Too many parameter sets given, %d out of %d expected parameter set%s %s been called.', count($this->applied), $this->number_of_configured_parameter_sets, $this->number_of_configured_parameter_sets !== 1 ? 's' : '', count($this->applied) !== 1 ? 'have' : 'has'));
        }
        foreach ($this->applied as $parameters) {
            $parameters->verify();
        }
    }
}