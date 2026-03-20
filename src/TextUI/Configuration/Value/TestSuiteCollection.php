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
namespace Php_Unit\Text_Ui\Configuration;

use function count;
use Countable;
use IteratorAggregate;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 *
 * @template-implements IteratorAggregate<non-negative-int, TestSuite>
 */
final readonly class Test_Suite_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<TestSuite>
     */
    private array $test_suites;
    /**
     * @param list<TestSuite> $testSuites
     */
    public static function from_array(array $test_suites): self
    {
        return new self(...$test_suites);
    }
    private function __construct(Test_Suite ...$test_suites)
    {
        $this->test_suites = $test_suites;
    }
    /**
     * @return list<TestSuite>
     */
    public function as_array(): array
    {
        return $this->test_suites;
    }
    public function count(): int
    {
        return count($this->test_suites);
    }
    public function getIterator(): Test_Suite_Collection_Iterator
    {
        return new Test_Suite_Collection_Iterator($this);
    }
    public function is_empty(): bool
    {
        return $this->count() === 0;
    }
}