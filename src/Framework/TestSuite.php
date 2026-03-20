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

use function array_all;
use function array_merge;
use function array_pop;
use function array_reverse;
use function assert;
use function call_user_func;
use function class_exists;
use function count;
use function implode;
use function is_callable;
use function is_file;
use function is_subclass_of;
use Iterator;
use IteratorAggregate;
use const PHP_EOL;
use Php_Unit\Event;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\No_Previous_Throwable_Exception;
use Php_Unit\Metadata\Api\Dependencies;
use Php_Unit\Metadata\Api\Groups;
use Php_Unit\Metadata\Api\Hook_Methods;
use Php_Unit\Metadata\Api\Requirements;
use Php_Unit\Metadata\Metadata_Collection;
use Php_Unit\Runner\Exception as RunnerException;
use Php_Unit\Runner\Filter\Factory;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use Php_Unit\Runner\Test_Suite_Loader;
use Php_Unit\Test_Runner\Test_Result\Facade as TestResultFacade;
use Php_Unit\Util\Filter;
use Php_Unit\Util\Reflection;
use Php_Unit\Util\Test as TestUtil;
use ReflectionClass;
use ReflectionMethod;
use Sebastian_Bergmann\Code_Coverage\InvalidArgumentException;
use Sebastian_Bergmann\Code_Coverage\Unintentionally_Covered_Code_Exception;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use Throwable;
use function trim;
/**
 * @template-implements IteratorAggregate<non-negative-int, Test>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class Test_Suite implements IteratorAggregate, Reorderable, Test
{
    /**
     * @var array<non-empty-string, list<non-empty-string>>
     */
    private array $groups = [];
    /**
     * @var ?list<ExecutionOrderDependency>
     */
    private ?array $required_tests = null;
    /**
     * @var list<Test>
     */
    private array $tests = [];
    /**
     * @var ?list<ExecutionOrderDependency>
     */
    private ?array $provided_tests = null;
    private ?Factory $iterator_filter = null;
    private bool $was_run = false;
    /**
     * @param non-empty-string $name
     */
    public static function empty(string $name): static
    {
        return new static($name);
    }
    /**
     * @param ReflectionClass<TestCase> $class
     * @param list<non-empty-string>    $groups
     */
    public static function from_class_reflector(ReflectionClass $class, array $groups = []): static
    {
        $test_suite = new static($class->get_name());
        foreach (Reflection::public_methods_declared_directly_in_test_class($class) as $method) {
            if (!Test_Util::is_test_method($method)) {
                continue;
            }
            if ((new Hook_Methods())->is_hook_method($method)) {
                Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Method %s::%s() cannot be used both as a hook method and as a test method', $class->get_name(), $method->get_name()));
                continue;
            }
            $test_suite->add_test_method($class, $method, $groups);
        }
        if ($test_suite->is_empty()) {
            Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('No tests found in class "%s".', $class->get_name()));
        }
        return $test_suite;
    }
    /**
     * @param non-empty-string $name
     */
    final private function __construct(private readonly string $name)
    {
    }
    /**
     * Adds a test to the suite.
     *
     * @param list<non-empty-string> $groups
     */
    public function add_test(Test $test, array $groups = []): void
    {
        if ($test instanceof self) {
            $this->tests[] = $test;
            $this->clear_caches();
            return;
        }
        assert($test instanceof Test_Case || $test instanceof Phpt_Test_Case);
        $this->tests[] = $test;
        $this->clear_caches();
        if ($this->contains_only_virtual_groups($groups)) {
            $groups[] = 'default';
        }
        if ($test instanceof Test_Case) {
            $id = $test->value_object_for_events()->id();
            $test->set_groups($groups);
        } else {
            $id = $test->value_object_for_events()->id();
        }
        foreach ($groups as $group) {
            if (!isset($this->groups[$group])) {
                $this->groups[$group] = [$id];
            } else {
                $this->groups[$group][] = $id;
            }
        }
    }
    /**
     * Adds the tests from the given class to the suite.
     *
     * @param ReflectionClass<TestCase> $testClass
     * @param list<non-empty-string>    $groups
     *
     * @throws Exception
     */
    public function add_test_suite(ReflectionClass $test_class, array $groups = []): void
    {
        if ($test_class->is_abstract()) {
            throw new Exception(sprintf('Class %s is abstract', $test_class->get_name()));
        }
        if (!$test_class->is_subclass_of(Test_Case::class)) {
            throw new Exception(sprintf('Class %s is not a subclass of %s', $test_class->get_name(), Test_Case::class));
        }
        $this->add_test(self::from_class_reflector($test_class, $groups), $groups);
    }
    /**
     * Wraps both <code>addTest()</code> and <code>addTestSuite</code>
     * as well as the separate import statements for the user's convenience.
     *
     * If the named file cannot be read or there are no new tests that can be
     * added, a <code>PHPUnit\Framework\WarningTestCase</code> will be created instead,
     * leaving the current test run untouched.
     *
     * @param list<non-empty-string> $groups
     *
     * @throws Exception
     */
    public function add_test_file(string $filename, array $groups = []): void
    {
        try {
            if (str_ends_with($filename, '.phpt') && is_file($filename)) {
                $this->add_test(new Phpt_Test_Case($filename));
            } else {
                $this->add_test_suite((new Test_Suite_Loader())->load($filename), $groups);
            }
        } catch (Runner_Exception $e) {
            Event\Facade::emitter()->test_runner_triggered_phpunit_warning($e->get_message());
        }
    }
    /**
     * Wrapper for addTestFile() that adds multiple test files.
     *
     * @param iterable<string> $fileNames
     *
     * @throws Exception
     */
    public function add_test_files(iterable $file_names): void
    {
        foreach ($file_names as $filename) {
            $this->add_test_file((string) $filename);
        }
    }
    /**
     * Counts the number of test cases that will be run by this test.
     */
    public function count(): int
    {
        $num_tests = 0;
        foreach ($this as $test) {
            $num_tests += count($test);
        }
        return $num_tests;
    }
    public function is_empty(): bool
    {
        foreach ($this as $test) {
            if (count($test) !== 0) {
                return false;
            }
        }
        return true;
    }
    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
    /**
     * @return array<non-empty-string, list<non-empty-string>>
     */
    public function groups(): array
    {
        return $this->groups;
    }
    /**
     * @return list<PhptTestCase|TestCase>
     */
    public function collect(): array
    {
        $tests = [];
        foreach ($this as $test) {
            if ($test instanceof self) {
                $tests = array_merge($tests, $test->collect());
                continue;
            }
            assert($test instanceof Test_Case || $test instanceof Phpt_Test_Case);
            $tests[] = $test;
        }
        return $tests;
    }
    /**
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(): void
    {
        if ($this->was_run) {
            // @codeCoverageIgnoreStart
            throw new Exception('The tests aggregated by this TestSuite were already run');
            // @codeCoverageIgnoreEnd
        }
        $this->was_run = true;
        if ($this->is_empty()) {
            return;
        }
        $emitter = Event\Facade::emitter();
        $test_suite_value_object_for_events = Event\Test_Suite\Test_Suite_Builder::from($this);
        $emitter->test_suite_started($test_suite_value_object_for_events);
        if (!$this->invoke_methods_before_first_test($emitter, $test_suite_value_object_for_events)) {
            return;
        }
        /** @var list<Test> $tests */
        $tests = [];
        foreach ($this as $test) {
            $tests[] = $test;
        }
        $tests = array_reverse($tests);
        $this->tests = [];
        $this->groups = [];
        while (($test = array_pop($tests)) !== null) {
            if (Test_Result_Facade::should_stop()) {
                $emitter->test_runner_execution_aborted();
                break;
            }
            $test->run();
        }
        $this->invoke_methods_after_last_test($emitter);
        $emitter->test_suite_finished($test_suite_value_object_for_events);
    }
    /**
     * Returns the tests as an enumeration.
     *
     * @return list<Test>
     */
    public function tests(): array
    {
        return $this->tests;
    }
    /**
     * Set tests of the test suite.
     *
     * @param list<Test> $tests
     */
    public function set_tests(array $tests): void
    {
        $this->tests = $tests;
    }
    /**
     * Mark the test suite as skipped.
     *
     * @throws SkippedTestSuiteError
     */
    public function mark_test_suite_skipped(string $message = ''): never
    {
        throw new Skipped_Test_Suite_Error($message);
    }
    /**
     * @return Iterator<non-negative-int, Test>
     */
    public function getIterator(): Iterator
    {
        $iterator = new Test_Suite_Iterator($this);
        if ($this->iterator_filter !== null) {
            return $this->iterator_filter->factory($iterator, $this);
        }
        return $iterator;
    }
    public function inject_filter(Factory $filter): void
    {
        $this->iterator_filter = $filter;
        foreach ($this as $test) {
            if ($test instanceof self) {
                $test->inject_filter($filter);
            }
        }
    }
    /**
     * @return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        if ($this->provided_tests === null) {
            $this->provided_tests = [];
            if (is_callable($this->sort_id(), true)) {
                $this->provided_tests[] = new Execution_Order_Dependency($this->sort_id());
            }
            foreach ($this->tests as $test) {
                if (!$test instanceof Reorderable) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                $this->provided_tests = Execution_Order_Dependency::merge_unique($this->provided_tests, $test->provides());
            }
        }
        return $this->provided_tests;
    }
    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        if ($this->required_tests === null) {
            $this->required_tests = [];
            foreach ($this->tests as $test) {
                if (!$test instanceof Reorderable) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                $this->required_tests = Execution_Order_Dependency::merge_unique(Execution_Order_Dependency::filter_invalid($this->required_tests), $test->requires());
            }
            $this->required_tests = Execution_Order_Dependency::diff($this->required_tests, $this->provides());
        }
        return $this->required_tests;
    }
    public function sort_id(): string
    {
        return $this->name() . '::class';
    }
    /**
     * @phpstan-assert-if-true class-string<TestCase> $this->name
     */
    public function is_for_test_class(): bool
    {
        return class_exists($this->name, false) && is_subclass_of($this->name, Test_Case::class);
    }
    /**
     * @param ReflectionClass<TestCase> $class
     * @param list<non-empty-string>    $groups
     *
     * @throws Exception
     */
    protected function add_test_method(ReflectionClass $class, ReflectionMethod $method, array $groups): void
    {
        $class_name = $class->get_name();
        $method_name = $method->get_name();
        try {
            $test = (new Test_Builder())->build($class, $method_name, $groups);
        } catch (Invalid_Data_Provider_Exception $e) {
            if ($e->get_provider_label() === null) {
                $message = sprintf("The data provider specified for %s::%s is invalid\n%s", $class_name, $method_name, $this->exception_to_string($e));
            } else {
                $message = sprintf("The data provider %s specified for %s::%s is invalid\n%s", $e->get_provider_label(), $class_name, $method_name, $this->exception_to_string($e));
            }
            Event\Facade::emitter()->test_triggered_phpunit_error(new Test_Method($class_name, $method_name, $method->get_file_name(), $method->get_start_line(), Event\Code\Test_Dox_Builder::from_class_name_and_method_name($class_name, $method_name), Metadata_Collection::from_array([]), Event\Test_Data\Test_Data_Collection::from_array([])), $message);
            return;
        }
        if ($test instanceof Test_Case || $test instanceof Data_Provider_Test_Suite) {
            $test->set_dependencies(Dependencies::dependencies($class->get_name(), $method_name));
        }
        $this->add_test($test, array_merge($groups, (new Groups())->groups($class->get_name(), $method_name)));
    }
    private function clear_caches(): void
    {
        $this->provided_tests = null;
        $this->required_tests = null;
    }
    /**
     * @param list<non-empty-string> $groups
     */
    private function contains_only_virtual_groups(array $groups): bool
    {
        return array_all($groups, static fn(string $group): bool => str_starts_with($group, '__phpunit_'));
    }
    private function method_does_not_exist_or_is_declared_in_test_case(string $method_name): bool
    {
        $reflector = new ReflectionClass($this->name);
        if (!$reflector->has_method($method_name)) {
            return true;
        }
        return $reflector->get_method($method_name)->get_declaring_class()->get_name() === Test_Case::class;
    }
    /**
     * @throws Exception
     */
    private function exception_to_string(Invalid_Data_Provider_Exception $e): string
    {
        $message = $e->get_message();
        if (trim($message) === '') {
            $message = '<no message>';
        }
        return sprintf("%s\n%s", $message, Filter::stack_trace_from_throwable_as_string($e));
    }
    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    private function invoke_methods_before_first_test(Event\Emitter $emitter, Event\Test_Suite\Test_Suite $test_suite_value_object_for_events): bool
    {
        if (!$this->is_for_test_class()) {
            return true;
        }
        $methods = (new Hook_Methods())->hook_methods($this->name)['beforeClass']->method_names_sorted_by_priority();
        $called_methods = [];
        $emit_called_event = true;
        $result = true;
        foreach ($methods as $method) {
            if ($this->method_does_not_exist_or_is_declared_in_test_case($method)) {
                continue;
            }
            $called_method = new Event\Code\Class_Method($this->name, $method);
            try {
                $missing_requirements = (new Requirements())->requirements_not_satisfied_for($this->name, $method);
                if ($missing_requirements !== []) {
                    $emit_called_event = false;
                    $this->mark_test_suite_skipped(implode(PHP_EOL, $missing_requirements));
                }
                call_user_func([$this->name, $method]);
            } catch (Throwable $t) {
            }
            if ($emit_called_event) {
                $emitter->before_first_test_method_called($this->name, $called_method);
                $called_methods[] = $called_method;
            }
            if (isset($t) && $t instanceof Skipped_Test) {
                $emitter->test_suite_skipped($test_suite_value_object_for_events, $t->get_message());
                return false;
            }
            if (isset($t)) {
                if ($t instanceof Assertion_Failed_Error) {
                    $emitter->before_first_test_method_failed($this->name, $called_method, Event\Code\Throwable_Builder::from($t));
                } else {
                    $emitter->before_first_test_method_errored($this->name, $called_method, Event\Code\Throwable_Builder::from($t));
                }
                $result = false;
            }
        }
        if ($called_methods !== []) {
            $emitter->before_first_test_method_finished($this->name, ...$called_methods);
        }
        if (!$result) {
            $emitter->test_suite_finished($test_suite_value_object_for_events);
        }
        return $result;
    }
    private function invoke_methods_after_last_test(Event\Emitter $emitter): void
    {
        if (!$this->is_for_test_class()) {
            return;
        }
        $methods = (new Hook_Methods())->hook_methods($this->name)['afterClass']->method_names_sorted_by_priority();
        $called_methods = [];
        foreach ($methods as $method) {
            if ($this->method_does_not_exist_or_is_declared_in_test_case($method)) {
                continue;
            }
            $called_method = new Event\Code\Class_Method($this->name, $method);
            try {
                call_user_func([$this->name, $method]);
            } catch (Throwable $t) {
            }
            $emitter->after_last_test_method_called($this->name, $called_method);
            $called_methods[] = $called_method;
            if (isset($t)) {
                if ($t instanceof Assertion_Failed_Error) {
                    $emitter->after_last_test_method_failed($this->name, $called_method, Event\Code\Throwable_Builder::from($t));
                } else {
                    $emitter->after_last_test_method_errored($this->name, $called_method, Event\Code\Throwable_Builder::from($t));
                }
            }
        }
        if ($called_methods !== []) {
            $emitter->after_last_test_method_finished($this->name, ...$called_methods);
        }
    }
}