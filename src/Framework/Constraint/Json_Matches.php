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

use function json_decode;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Util\Invalid_Json_Exception;
use Php_Unit\Util\Json;
use Sebastian_Bergmann\Comparator\Comparison_Failure;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Json_Matches extends Constraint
{
    public function __construct(private readonly string $value)
    {
    }
    /**
     * Returns a string representation of the object.
     */
    public function to_string(): string
    {
        return sprintf('matches JSON string "%s"', $this->value);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * This method can be overridden to implement the evaluation algorithm.
     */
    protected function matches(mixed $other): bool
    {
        [$error, $recoded_other] = Json::canonicalize($other);
        if ($error) {
            return false;
        }
        [$error, $recoded_value] = Json::canonicalize($this->value);
        if ($error) {
            return false;
        }
        return $recoded_other === $recoded_value;
    }
    /**
     * Throws an exception for the given compared value and test description.
     *
     * @throws ExpectationFailedException
     * @throws InvalidJsonException
     */
    protected function fail(mixed $other, string $description, ?Comparison_Failure $comparison_failure = null): never
    {
        if ($comparison_failure === null) {
            [$error, $recoded_other] = Json::canonicalize($other);
            if ($error) {
                parent::fail($other, $description);
            }
            [$error, $recoded_value] = Json::canonicalize($this->value);
            if ($error) {
                parent::fail($other, $description);
            }
            $comparison_failure = new Comparison_Failure(json_decode($this->value), json_decode((string) $other), Json::prettify($recoded_value), Json::prettify($recoded_other), 'Failed asserting that two json values are equal.');
        }
        parent::fail($other, $description, $comparison_failure);
    }
}