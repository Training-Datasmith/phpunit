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
namespace Php_Unit\Framework;

use Exception;
use Sebastian_Bergmann\Comparator\Comparison_Failure;
/**
 * Exception for expectations which failed their check.
 *
 * The exception contains the error message and optionally a
 * SebastianBergmann\Comparator\ComparisonFailure which is used to
 * generate diff output of the failed expectations.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Expectation_Failed_Exception extends Assertion_Failed_Error
{
    public function __construct(string $message, protected ?Comparison_Failure $comparison_failure = null, ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
    public function get_comparison_failure(): ?Comparison_Failure
    {
        return $this->comparison_failure;
    }
}