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
 * @template-implements IteratorAggregate<non-negative-int, Directory>
 */
final readonly class Directory_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<Directory>
     */
    private array $directories;
    /**
     * @param list<Directory> $directories
     */
    public static function from_array(array $directories): self
    {
        return new self(...$directories);
    }
    private function __construct(Directory ...$directories)
    {
        $this->directories = $directories;
    }
    /**
     * @return list<Directory>
     */
    public function as_array(): array
    {
        return $this->directories;
    }
    public function count(): int
    {
        return count($this->directories);
    }
    public function getIterator(): Directory_Collection_Iterator
    {
        return new Directory_Collection_Iterator($this);
    }
    public function is_empty(): bool
    {
        return $this->count() === 0;
    }
}