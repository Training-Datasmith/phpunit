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
namespace Php_Unit\Runner\Filter;

use function assert;
use Filter_Iterator;
use Iterator;
use Php_Unit\Framework\Test;
use Php_Unit\Framework\Test_Suite;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Factory
{
    /**
     * @var list<array{className: class-string<FilterIterator<int, Test, Iterator<int, Test>>>, argument: list<non-empty-string>|non-empty-string}>
     */
    private array $filters = [];
    /**
     * @param list<non-empty-string> $testIds
     */
    public function add_test_id_filter(array $test_ids): void
    {
        $this->filters[] = ['className' => Test_Id_Filter_Iterator::class, 'argument' => $test_ids];
    }
    /**
     * @param list<non-empty-string> $groups
     */
    public function add_include_group_filter(array $groups): void
    {
        $this->filters[] = ['className' => Include_Group_Filter_Iterator::class, 'argument' => $groups];
    }
    /**
     * @param list<non-empty-string> $groups
     */
    public function add_exclude_group_filter(array $groups): void
    {
        $this->filters[] = ['className' => Exclude_Group_Filter_Iterator::class, 'argument' => $groups];
    }
    /**
     * @param non-empty-string $name
     */
    public function add_include_name_filter(string $name): void
    {
        $this->filters[] = ['className' => Include_Name_Filter_Iterator::class, 'argument' => $name];
    }
    /**
     * @param non-empty-string $name
     */
    public function add_exclude_name_filter(string $name): void
    {
        $this->filters[] = ['className' => Exclude_Name_Filter_Iterator::class, 'argument' => $name];
    }
    /**
     * @param Iterator<int, Test> $iterator
     *
     * @return FilterIterator<int, Test, Iterator<int, Test>>
     */
    public function factory(Iterator $iterator, Test_Suite $suite): Filter_Iterator
    {
        foreach ($this->filters as $filter) {
            $iterator = new $filter['className']($iterator, $filter['argument'], $suite);
        }
        assert($iterator instanceof Filter_Iterator);
        return $iterator;
    }
}