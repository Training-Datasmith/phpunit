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

use function explode;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Util\Exporter;
use Sebastian_Bergmann\Comparator\Comparison_Failure;
use function sprintf;
use Unit_Enum;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Is_Identical extends Constraint
{
    public function __construct(private readonly mixed $value)
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
        $success = $this->value === $other;
        if ($return_result) {
            return $success;
        }
        if (!$success) {
            $f = null;
            // if both values are strings, make sure a diff is generated
            if (is_string($this->value) && is_string($other)) {
                $f = new Comparison_Failure($this->value, $other, sprintf("'%s'", $this->value), sprintf("'%s'", $other));
            }
            // if both values are array or enums, make sure a diff is generated
            if (is_array($this->value) && is_array($other) || $this->value instanceof Unit_Enum && $other instanceof Unit_Enum) {
                $f = new Comparison_Failure($this->value, $other, Exporter::export($this->value), Exporter::export($other));
            }
            $this->fail($other, $description, $f);
        }
        return null;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        if (is_object($this->value)) {
            return 'is identical to an object of class "' . $this->value::class . '"';
        }
        return 'is identical to ' . Exporter::export($this->value);
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failure_description(mixed $other): string
    {
        if (is_object($this->value) && is_object($other)) {
            return 'two variables reference the same object';
        }
        if (explode(' ', gettype($this->value), 2)[0] === 'resource' && explode(' ', gettype($other), 2)[0] === 'resource') {
            return 'two variables reference the same resource';
        }
        if (is_string($this->value) && is_string($other)) {
            return 'two strings are identical';
        }
        if (is_array($this->value) && is_array($other)) {
            return 'two arrays are identical';
        }
        return parent::failure_description($other);
    }
}