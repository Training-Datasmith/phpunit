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
 * @template-implements IteratorAggregate<non-negative-int, FilterFile>
 */
final readonly class Filter_File_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<FilterFile>
     */
    private array $files;
    /**
     * @param list<FilterFile> $files
     */
    public static function from_array(array $files): self
    {
        return new self(...$files);
    }
    private function __construct(Filter_File ...$files)
    {
        $this->files = $files;
    }
    /**
     * @return list<FilterFile>
     */
    public function as_array(): array
    {
        return $this->files;
    }
    public function count(): int
    {
        return count($this->files);
    }
    public function not_empty(): bool
    {
        return $this->files !== [];
    }
    public function getIterator(): Filter_File_Collection_Iterator
    {
        return new Filter_File_Collection_Iterator($this);
    }
}