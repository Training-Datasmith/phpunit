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
namespace Php_Unit\Framework\Constraint;

use function array_keys;
use function array_values;
use function is_array;
use function ksort;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Util\Exporter;
use Sebastian_Bergmann\Comparator\Comparison_Failure;
use function sort;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class Array_Comparison extends Constraint
{
    /**
     * @param array<mixed> $expected
     */
    public function __construct(protected readonly array $expected, protected readonly bool $keys_matter, protected readonly bool $order_matters)
    {
    }
    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws ExpectationFailedException
     */
    public function evaluate(mixed $other, string $description = '', bool $return_result = false): ?bool
    {
        if (!is_array($other)) {
            return false;
        }
        $expected = $this->expected;
        $actual = $other;
        if ($this->keys_matter && !$this->order_matters) {
            $expected_keys = array_keys($expected);
            $actual_keys = array_keys($actual);
            sort($expected_keys);
            sort($actual_keys);
            if ($expected_keys === $actual_keys) {
                sort($expected);
                sort($actual);
            } else {
                ksort($expected);
                ksort($actual);
            }
        }
        if (!$this->keys_matter) {
            $expected = array_values($expected);
            $actual = array_values($actual);
            if (!$this->order_matters) {
                sort($expected);
                sort($actual);
            }
        }
        $success = $this->compare_arrays($expected, $actual);
        if ($return_result) {
            return $success;
        }
        if (!$success) {
            $this->fail($other, $description, new Comparison_Failure($this->expected, $other, Exporter::export($this->expected), Exporter::export($other)));
        }
        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        if ($this->keys_matter && $this->order_matters) {
            return 'two arrays are ' . $this->comparison_type();
        }
        if (!$this->keys_matter && !$this->order_matters) {
            return 'two arrays are ' . $this->comparison_type() . ' while ignoring keys and order';
        }
        if (!$this->keys_matter) {
            return 'two arrays are ' . $this->comparison_type() . ' while ignoring keys';
        }
        return 'two arrays are ' . $this->comparison_type() . ' while ignoring order';
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failure_description(mixed $other): string
    {
        return $this->to_string();
    }
    /**
     * Compares two arrays using the appropriate comparison method.
     */
    abstract protected function compare_arrays(mixed $expected, mixed $actual): bool;
    /**
     * @return 'equal'|'identical'
     */
    abstract protected function comparison_type(): string;
}