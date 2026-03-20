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
namespace Php_Unit\Logging\Test_Dox;

use IteratorAggregate;
/**
 * @template-implements IteratorAggregate<non-negative-int, TestResult>
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Result_Collection implements IteratorAggregate
{
    /**
     * @var list<TestResult>
     */
    private array $test_results;
    /**
     * @param list<TestResult> $testResults
     */
    public static function from_array(array $test_results): self
    {
        return new self(...$test_results);
    }
    private function __construct(Test_Result ...$test_results)
    {
        $this->test_results = $test_results;
    }
    /**
     * @return list<TestResult>
     */
    public function as_array(): array
    {
        return $this->test_results;
    }
    public function getIterator(): Test_Result_Collection_Iterator
    {
        return new Test_Result_Collection_Iterator($this);
    }
}