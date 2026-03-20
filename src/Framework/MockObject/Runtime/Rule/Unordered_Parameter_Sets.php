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

use function array_search;
use function array_shift;
use function count;
use function implode;
use function is_array;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Mock_Object\Invocation as BaseInvocation;
use Php_Unit\Framework\Mock_Object\No_More_Parameter_Sets_Configured_Exception;
use function sprintf;
final class Unordered_Parameter_Sets implements Parameters_Rule
{
    /**
     * @var list<Parameters>
     */
    private array $stack = [];
    /**
     * @var list<Parameters>
     */
    private array $unapplied = [];
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
        $this->unapplied = $this->stack;
        $this->number_of_configured_parameter_sets = count($stack);
    }
    public function apply(Base_Invocation $invocation): void
    {
        if ($this->unapplied === []) {
            throw new No_More_Parameter_Sets_Configured_Exception($invocation, $this->number_of_configured_parameter_sets);
        }
        $checked_parameters = 0;
        $unapplied_parameters = count($this->unapplied);
        while ($checked_parameters < $unapplied_parameters) {
            $checked_parameters++;
            $parameters = array_shift($this->unapplied);
            try {
                $parameters->use_assertion_count(false);
                $parameters->apply($invocation);
                $this->applied[] = $parameters;
                $parameters->use_assertion_count(true);
                $parameters->apply($invocation);
                break;
            } catch (Expectation_Failed_Exception) {
                $this->unapplied[] = $parameters;
            }
        }
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
        if (count($this->applied) !== $this->number_of_configured_parameter_sets && count($this->unapplied) > 0) {
            $unapplied_indexes = [];
            foreach ($this->unapplied as $parameters) {
                $unapplied_indexes[] = array_search($parameters, $this->stack, true);
            }
            throw new Expectation_Failed_Exception(sprintf('%d out of %d expected parameter set%s %s called, index%s [' . implode(', ', $unapplied_indexes) . '] %s not called.', count($this->applied), $this->number_of_configured_parameter_sets, $this->number_of_configured_parameter_sets !== 1 ? 's' : '', count($this->applied) !== 1 ? 'were' : 'was', count($unapplied_indexes) !== 1 ? 'es' : '', count($unapplied_indexes) !== 1 ? 'were' : 'was'));
        }
    }
}