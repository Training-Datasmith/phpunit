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

use Iterator;
/**
 * @template-implements Iterator<non-negative-int, TestResult>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Result_Collection_Iterator implements Iterator
{
    /**
     * @var list<TestResult>
     */
    private readonly array $test_results;
    /**
     * @var non-negative-int
     */
    private int $position = 0;
    public function __construct(Test_Result_Collection $test_results)
    {
        $this->test_results = $test_results->as_array();
    }
    public function rewind(): void
    {
        $this->position = 0;
    }
    public function valid(): bool
    {
        return isset($this->test_results[$this->position]);
    }
    /**
     * @return non-negative-int
     */
    public function key(): int
    {
        return $this->position;
    }
    public function current(): Test_Result
    {
        return $this->test_results[$this->position];
    }
    public function next(): void
    {
        $this->position++;
    }
}