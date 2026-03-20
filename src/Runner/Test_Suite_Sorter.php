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
namespace Php_Unit\Runner;

use function array_diff;
use function array_merge;
use function array_reverse;
use function array_splice;
use function assert;
use function count;
use function in_array;
use function max;
use Php_Unit\Framework\Data_Provider_Test_Suite;
use Php_Unit\Framework\Reorderable;
use Php_Unit\Framework\Test;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Result_Cache\Null_Result_Cache;
use Php_Unit\Runner\Result_Cache\Result_Cache;
use Php_Unit\Runner\Result_Cache\Result_Cache_Id;
use function shuffle;
use function usort;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Suite_Sorter
{
    public const int ORDER_DEFAULT = 0;
    public const int ORDER_RANDOMIZED = 1;
    public const int ORDER_REVERSED = 2;
    public const int ORDER_DEFECTS_FIRST = 3;
    public const int ORDER_DURATION = 4;
    public const int ORDER_SIZE = 5;
    /**
     * @var non-empty-array<non-empty-string, positive-int>
     */
    private const array SIZE_SORT_WEIGHT = ['small' => 1, 'medium' => 2, 'large' => 3, 'unknown' => 4];
    /**
     * @var array<string, int> Associative array of (string => DEFECT_SORT_WEIGHT) elements
     */
    private array $defect_sort_order = [];
    public function __construct(private readonly ?Result_Cache $cache = new Null_Result_Cache())
    {
    }
    /**
     * @throws Exception
     */
    public function reorder_tests_in_suite(Test $suite, int $order, bool $resolve_dependencies, int $order_defects): void
    {
        $allowed_orders = [self::ORDER_DEFAULT, self::ORDER_REVERSED, self::ORDER_RANDOMIZED, self::ORDER_DURATION, self::ORDER_SIZE];
        if (!in_array($order, $allowed_orders, true)) {
            // @codeCoverageIgnoreStart
            throw new Invalid_Order_Exception();
            // @codeCoverageIgnoreEnd
        }
        $allowed_order_defects = [self::ORDER_DEFAULT, self::ORDER_DEFECTS_FIRST];
        if (!in_array($order_defects, $allowed_order_defects, true)) {
            // @codeCoverageIgnoreStart
            throw new Invalid_Order_Exception();
            // @codeCoverageIgnoreEnd
        }
        if ($suite instanceof Test_Suite) {
            foreach ($suite as $_suite) {
                $this->reorder_tests_in_suite($_suite, $order, $resolve_dependencies, $order_defects);
            }
            if ($order_defects === self::ORDER_DEFECTS_FIRST) {
                $this->add_suite_to_defect_sort_order($suite);
            }
            $this->sort($suite, $order, $resolve_dependencies, $order_defects);
        }
    }
    private function sort(Test_Suite $suite, int $order, bool $resolve_dependencies, int $order_defects): void
    {
        if ($suite->tests() === []) {
            return;
        }
        if ($order === self::ORDER_REVERSED) {
            $suite->set_tests($this->reverse($suite->tests()));
        } elseif ($order === self::ORDER_RANDOMIZED) {
            $suite->set_tests($this->randomize($suite->tests()));
        } elseif ($order === self::ORDER_DURATION) {
            $suite->set_tests($this->sort_by_duration($suite->tests()));
        } elseif ($order === self::ORDER_SIZE) {
            $suite->set_tests($this->sort_by_size($suite->tests()));
        }
        if ($order_defects === self::ORDER_DEFECTS_FIRST) {
            $suite->set_tests($this->sort_defects_first($suite->tests()));
        }
        if ($resolve_dependencies && !$suite instanceof Data_Provider_Test_Suite) {
            $tests = $suite->tests();
            /** @noinspection PhpParamsInspection */
            /** @phpstan-ignore argument.type */
            $suite->set_tests($this->resolve_dependencies($tests));
        }
    }
    private function add_suite_to_defect_sort_order(Test_Suite $suite): void
    {
        $max = 0;
        foreach ($suite->tests() as $test) {
            if (!$test instanceof Reorderable) {
                continue;
            }
            $sort_id = $test->sort_id();
            if (!isset($this->defect_sort_order[$sort_id])) {
                $this->defect_sort_order[$sort_id] = $this->cache->status(Result_Cache_Id::from_reorderable($test))->as_int();
                $max = max($max, $this->defect_sort_order[$sort_id]);
            }
        }
        $this->defect_sort_order[$suite->sort_id()] = $max;
    }
    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    private function reverse(array $tests): array
    {
        return array_reverse($tests);
    }
    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    private function randomize(array $tests): array
    {
        shuffle($tests);
        return $tests;
    }
    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    private function sort_defects_first(array $tests): array
    {
        usort($tests, fn(Test $left, Test $right): int => $this->cmp_defect_priority_and_time($left, $right));
        return $tests;
    }
    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    private function sort_by_duration(array $tests): array
    {
        usort($tests, fn(Test $left, Test $right): int => $this->cmp_duration($left, $right));
        return $tests;
    }
    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    private function sort_by_size(array $tests): array
    {
        usort($tests, fn(Test $left, Test $right): int => $this->cmp_size($left, $right));
        return $tests;
    }
    /**
     * Comparator callback function to sort tests for "reach failure as fast as possible".
     *
     * 1. sort tests by defect weight defined in self::DEFECT_SORT_WEIGHT
     * 2. when tests are equally defective, sort the fastest to the front
     * 3. do not reorder successful tests
     */
    private function cmp_defect_priority_and_time(Test $a, Test $b): int
    {
        assert($a instanceof Reorderable);
        assert($b instanceof Reorderable);
        $priority_a = $this->defect_sort_order[$a->sort_id()] ?? 0;
        $priority_b = $this->defect_sort_order[$b->sort_id()] ?? 0;
        if ($priority_a !== $priority_b) {
            // Sort defect weight descending
            return $priority_b <=> $priority_a;
        }
        if ($priority_a > 0 || $priority_b > 0) {
            return $this->cmp_duration($a, $b);
        }
        // do not change execution order
        return 0;
    }
    /**
     * Compares test duration for sorting tests by duration ascending.
     */
    private function cmp_duration(Test $a, Test $b): int
    {
        if (!($a instanceof Reorderable && $b instanceof Reorderable)) {
            return 0;
        }
        return $this->cache->time(Result_Cache_Id::from_reorderable($a)) <=> $this->cache->time(Result_Cache_Id::from_reorderable($b));
    }
    /**
     * Compares test size for sorting tests small->medium->large->unknown.
     */
    private function cmp_size(Test $a, Test $b): int
    {
        $size_a = $a instanceof Test_Case || $a instanceof Data_Provider_Test_Suite ? $a->size()->as_string() : 'unknown';
        $size_b = $b instanceof Test_Case || $b instanceof Data_Provider_Test_Suite ? $b->size()->as_string() : 'unknown';
        return self::SIZE_SORT_WEIGHT[$size_a] <=> self::SIZE_SORT_WEIGHT[$size_b];
    }
    /**
     * Reorder Tests within a TestCase in such a way as to resolve as many dependencies as possible.
     * The algorithm will leave the tests in original running order when it can.
     * For more details see the documentation for test dependencies.
     *
     * Short description of algorithm:
     * 1. Pick the next Test from remaining tests to be checked for dependencies.
     * 2. If the test has no dependencies: mark done, start again from the top
     * 3. If the test has dependencies but none left to do: mark done, start again from the top
     * 4. When we reach the end add any leftover tests to the end. These will be marked 'skipped' during execution.
     *
     * @param array<TestCase> $tests
     *
     * @return array<TestCase>
     */
    private function resolve_dependencies(array $tests): array
    {
        $new_test_order = [];
        $i = 0;
        $provided = [];
        do {
            if ([] === array_diff($tests[$i]->requires(), $provided)) {
                $provided = array_merge($provided, $tests[$i]->provides());
                $new_test_order = array_merge($new_test_order, array_splice($tests, $i, 1));
                $i = 0;
            } else {
                $i++;
            }
        } while ($tests !== [] && $i < count($tests));
        return array_merge($new_test_order, $tests);
    }
}