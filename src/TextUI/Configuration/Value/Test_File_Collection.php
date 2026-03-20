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
 * @template-implements IteratorAggregate<non-negative-int, TestFile>
 */
final readonly class Test_File_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<TestFile>
     */
    private array $files;
    /**
     * @param list<TestFile> $files
     */
    public static function from_array(array $files): self
    {
        return new self(...$files);
    }
    private function __construct(Test_File ...$files)
    {
        $this->files = $files;
    }
    /**
     * @return list<TestFile>
     */
    public function as_array(): array
    {
        return $this->files;
    }
    public function count(): int
    {
        return count($this->files);
    }
    public function getIterator(): Test_File_Collection_Iterator
    {
        return new Test_File_Collection_Iterator($this);
    }
    public function is_empty(): bool
    {
        return $this->count() === 0;
    }
}