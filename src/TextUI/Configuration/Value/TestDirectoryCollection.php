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
 * @template-implements IteratorAggregate<non-negative-int, TestDirectory>
 */
final readonly class Test_Directory_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<TestDirectory>
     */
    private array $directories;
    /**
     * @param list<TestDirectory> $directories
     */
    public static function from_array(array $directories): self
    {
        return new self(...$directories);
    }
    private function __construct(Test_Directory ...$directories)
    {
        $this->directories = $directories;
    }
    /**
     * @return list<TestDirectory>
     */
    public function as_array(): array
    {
        return $this->directories;
    }
    public function count(): int
    {
        return count($this->directories);
    }
    public function getIterator(): Test_Directory_Collection_Iterator
    {
        return new Test_Directory_Collection_Iterator($this);
    }
    public function is_empty(): bool
    {
        return $this->count() === 0;
    }
}