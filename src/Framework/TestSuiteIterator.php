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

use function assert;
use Recursive_Iterator;
/**
 * @template-implements RecursiveIterator<non-negative-int, Test>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Suite_Iterator implements Recursive_Iterator
{
    /**
     * @var list<Test>
     */
    private readonly array $tests;
    /**
     * @var non-negative-int
     */
    private int $position = 0;
    public function __construct(Test_Suite $test_suite)
    {
        $this->tests = $test_suite->tests();
    }
    public function rewind(): void
    {
        $this->position = 0;
    }
    public function valid(): bool
    {
        return isset($this->tests[$this->position]);
    }
    /**
     * @return non-negative-int
     */
    public function key(): int
    {
        return $this->position;
    }
    public function current(): Test
    {
        return $this->tests[$this->position];
    }
    public function next(): void
    {
        $this->position++;
    }
    /**
     * @throws NoChildTestSuiteException
     */
    public function get_children(): self
    {
        if (!$this->has_children()) {
            throw new No_Child_Test_Suite_Exception('The current item is not a TestSuite instance and therefore does not have any children.');
        }
        $current = $this->current();
        assert($current instanceof Test_Suite);
        return new self($current);
    }
    public function has_children(): bool
    {
        return $this->valid() && $this->current() instanceof Test_Suite;
    }
}