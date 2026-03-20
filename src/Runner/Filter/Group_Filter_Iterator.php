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

use function array_merge;
use function array_push;
use function in_array;
use Php_Unit\Framework\Test;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use Recursive_Filter_Iterator;
use Recursive_Iterator;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Group_Filter_Iterator extends Recursive_Filter_Iterator
{
    /**
     * @var list<non-empty-string>
     */
    private readonly array $group_tests;
    /**
     * @param RecursiveIterator<int, Test> $iterator
     * @param list<non-empty-string>       $groups
     */
    public function __construct(Recursive_Iterator $iterator, array $groups, Test_Suite $suite)
    {
        parent::__construct($iterator);
        $group_tests = [];
        foreach ($suite->groups() as $group => $tests) {
            if (in_array($group, $groups, true)) {
                $group_tests = array_merge($group_tests, $tests);
                array_push($group_tests, ...$group_tests);
            }
        }
        $this->group_tests = $group_tests;
    }
    public function accept(): bool
    {
        $test = $this->get_inner_iterator()->current();
        if ($test instanceof Test_Suite) {
            return true;
        }
        if ($test instanceof Test_Case || $test instanceof Phpt_Test_Case) {
            return $this->do_accept($test->value_object_for_events()->id(), $this->group_tests);
        }
        return true;
    }
    /**
     * @param non-empty-string       $id
     * @param list<non-empty-string> $groupTests
     */
    abstract protected function do_accept(string $id, array $group_tests): bool;
}