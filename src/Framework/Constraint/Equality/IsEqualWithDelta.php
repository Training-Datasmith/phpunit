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

use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Util\Exporter;
use Sebastian_Bergmann\Comparator\Comparison_Failure;
use Sebastian_Bergmann\Comparator\Factory as ComparatorFactory;
use function sprintf;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Is_Equal_With_Delta extends Constraint
{
    public function __construct(private readonly mixed $value, private readonly float $delta)
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
    public function evaluate(mixed $other, string $description = '', bool $return_result = false): bool
    {
        // If $this->value and $other are identical, they are also equal.
        // This is the most common path and will allow us to skip
        // initialization of all the comparators.
        if ($this->value === $other) {
            return true;
        }
        $comparator_factory = Comparator_Factory::get_instance();
        try {
            $comparator = $comparator_factory->get_comparator_for($this->value, $other);
            $comparator->assert_equals($this->value, $other, $this->delta);
        } catch (Comparison_Failure $f) {
            if ($return_result) {
                return false;
            }
            throw new Expectation_Failed_Exception(trim($description . "\n" . $f->get_message()), $f);
        }
        return true;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return sprintf('is equal to %s with delta <%F>', Exporter::export($this->value), $this->delta);
    }
}