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

use IteratorAggregate;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 *
 * @template-implements IteratorAggregate<non-negative-int, Group>
 */
final readonly class Group_Collection implements IteratorAggregate
{
    /**
     * @var list<Group>
     */
    private array $groups;
    /**
     * @param list<Group> $groups
     */
    public static function from_array(array $groups): self
    {
        return new self(...$groups);
    }
    private function __construct(Group ...$groups)
    {
        $this->groups = $groups;
    }
    /**
     * @return list<Group>
     */
    public function as_array(): array
    {
        return $this->groups;
    }
    /**
     * @return list<string>
     */
    public function as_array_of_strings(): array
    {
        $result = [];
        foreach ($this->groups as $group) {
            $result[] = $group->name();
        }
        return $result;
    }
    public function is_empty(): bool
    {
        return $this->groups === [];
    }
    public function getIterator(): Group_Collection_Iterator
    {
        return new Group_Collection_Iterator($this);
    }
}