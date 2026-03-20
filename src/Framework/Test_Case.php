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

use function array_any;
use function array_keys;
use function array_merge;
use function array_reverse;
use function array_values;
use function assert;
use AssertionError;
use function chdir;
use function class_exists;
use function clearstatcache;
use function count;
use Deep_Copy\Deep_Copy;
use function defined;
use function error_clear_last;
use function explode;
use function fclose;
use function getcwd;
use function implode;
use function in_array;
use function ini_get;
use function ini_set;
use function is_array;
use function is_callable;
use function is_int;
use function is_object;
use function is_string;
use function is_writable;
use function libxml_clear_errors;
use function method_exists;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_contents;
use function ob_get_level;
use function ob_start;
use const PHP_EOL;
use Php_Unit\Event;
use Php_Unit\Event\No_Previous_Throwable_Exception;
use Php_Unit\Framework\Constraint\Exception as ExceptionConstraint;
use Php_Unit\Framework\Constraint\Exception_Code;
use Php_Unit\Framework\Constraint\Exception_Message_Is_Or_Contains;
use Php_Unit\Framework\Constraint\Exception_Message_Matches_Regular_Expression;
use Php_Unit\Framework\Mock_Object\Exception as MockObjectException;
use Php_Unit\Framework\Mock_Object\Generator\Generator as MockGenerator;
use Php_Unit\Framework\Mock_Object\Mock_Builder;
use Php_Unit\Framework\Mock_Object\Mock_Object;
use Php_Unit\Framework\Mock_Object\Mock_Object_Internal;
use Php_Unit\Framework\Mock_Object\Rule\Any_Invoked_Count as AnyInvokedCountMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Least_Count as InvokedAtLeastCountMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Least_Once as InvokedAtLeastOnceMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Most_Count as InvokedAtMostCountMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_Count;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_Count as InvokedCountMatcher;
use Php_Unit\Framework\Mock_Object\Stub;
use Php_Unit\Framework\Mock_Object\Stub\Exception as ExceptionStub;
use Php_Unit\Framework\Mock_Object\Test_Stub_Builder;
use Php_Unit\Framework\Test_Size\Test_Size;
use Php_Unit\Framework\Test_Status\Test_Status;
use Php_Unit\Metadata\Api\Groups;
use Php_Unit\Metadata\Api\Hook_Methods;
use Php_Unit\Metadata\Api\Requirements;
use Php_Unit\Metadata\Parser\Registry as MetadataRegistry;
use Php_Unit\Metadata\With_Environment_Variable;
use Php_Unit\Runner\Backed_Up_Environment_Variable;
use Php_Unit\Runner\Deprecation_Collector\Facade as DeprecationCollector;
use Php_Unit\Runner\Hook_Method_Collection;
use Php_Unit\Runner\Shutdown_Handler;
use Php_Unit\Test_Runner\Test_Result\Passed_Tests;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use Php_Unit\Util\Exporter;
use Php_Unit\Util\Test as TestUtil;
use function preg_match;
use function preg_replace;
use function putenv;
use ReflectionClass;
use Reflection_Exception;
use ReflectionMethod;
use Reflection_Object;
use function restore_error_handler;
use function restore_exception_handler;
use Sebastian_Bergmann\Code_Coverage\Unintentionally_Covered_Code_Exception;
use Sebastian_Bergmann\Comparator\Comparator;
use Sebastian_Bergmann\Comparator\Factory as ComparatorFactory;
use Sebastian_Bergmann\Diff\Differ;
use Sebastian_Bergmann\Diff\Output\Unified_Diff_Output_Builder;
use Sebastian_Bergmann\Global_State\Exclude_List as GlobalStateExcludeList;
use Sebastian_Bergmann\Global_State\Restorer;
use Sebastian_Bergmann\Global_State\Snapshot;
use Sebastian_Bergmann\Invoker\Timeout_Exception;
use Sebastian_Bergmann\Object_Enumerator\Enumerator;
use function set_error_handler;
use function set_exception_handler;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function stream_get_contents;
use function stream_get_meta_data;
use Throwable;
use function tmpfile;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class Test_Case extends Assert implements Reorderable, Self_Describing, Test
{
    private ?bool $backup_globals = null;
    /**
     * @var list<string>
     */
    private array $backup_globals_exclude_list = [];
    private ?bool $backup_static_properties = null;
    /**
     * @var array<string,list<class-string>>
     */
    private array $backup_static_properties_exclude_list = [];
    private ?Snapshot $snapshot = null;
    /**
     * @var list<callable>
     */
    private ?array $backup_global_error_handlers = null;
    /**
     * @var list<callable>
     */
    private ?array $backup_global_exception_handlers = null;
    private ?bool $run_test_in_separate_process = null;
    private bool $preserve_global_state = false;
    private bool $in_isolation = false;
    private ?string $expected_exception = null;
    private ?string $expected_exception_message = null;
    private ?string $expected_exception_message_reg_exp = null;
    private null|int|string $expected_exception_code = null;
    /**
     * @var list<BackedUpEnvironmentVariable>
     */
    private array $backup_environment_variables = [];
    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $provided_tests = [];
    /**
     * @var array<mixed>
     */
    private array $data = [];
    private int|string $data_name = '';
    /**
     * @var list<string>
     */
    private array $groups = [];
    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];
    /**
     * @var array<non-empty-string, array<mixed>>
     */
    private array $dependency_input = [];
    /**
     * @var list<array{type: non-empty-string, mockObject: MockObjectInternal}>
     */
    private array $mock_objects = [];
    private Test_Status $status;
    /**
     * @var non-negative-int
     */
    private int $number_of_assertions_performed = 0;
    private mixed $test_result = null;
    private string $output = '';
    private ?string $output_expected_regex = null;
    private ?string $output_expected_string = null;
    private bool $output_buffering_active = false;
    private int $output_buffering_level;
    private bool $output_retrieved_for_assertion = false;
    private bool $does_not_perform_assertions = false;
    private bool $expect_error_log = false;
    /**
     * @var list<Comparator>
     */
    private array $custom_comparators = [];
    private ?Event\Code\Test_Method $test_value_object_for_events = null;
    private bool $was_prepared = false;
    /**
     * @var array<class-string, true>
     */
    private array $failure_types = [];
    /**
     * @var list<non-empty-string>
     */
    private array $expected_user_deprecation_message = [];
    /**
     * @var list<non-empty-string>
     */
    private array $expected_user_deprecation_message_regular_expression = [];
    /**
     * @var false|resource
     */
    private mixed $error_log_capture = false;
    private false|string $previous_error_log_target = false;
    /**
     * @param non-empty-string $methodName
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function __construct(private readonly string $method_name)
    {
        $this->status = Test_Status::unknown();
        if (is_callable($this->sort_id(), true)) {
            $this->provided_tests = [new Execution_Order_Dependency($this->sort_id())];
        }
    }
    /**
     * Called once before the first test method in this class is run.
     *
     * Use this hook to set up expensive shared fixtures that are read-only
     * during the test run (e.g. database schema creation, external service
     * connections). Avoid storing mutable state here — use {@see setUp()} for
     * per-test state to prevent cross-test pollution.
     *
     * Throwing any exception from this method marks all tests in the class as
     * errored and skips their execution.
     *
     * @since 4.0
     *
     * @codeCoverageIgnore
     */
    public static function set_up_before_class(): void
    {
    }
    /**
     * Called once after the last test method in this class has run.
     *
     * Use this hook to release resources acquired in {@see setUpBeforeClass()}
     * (e.g. close database connections, delete temporary files).
     *
     * This method is called even if one or more tests threw exceptions,
     * so it is the right place for unconditional cleanup.
     *
     * @since 4.0
     *
     * @codeCoverageIgnore
     */
    public static function tear_down_after_class(): void
    {
    }
    /**
     * Called before each individual test method.
     *
     * Use this hook to reset mutable state and create fresh collaborators for
     * each test, ensuring full isolation. Any exception thrown here causes the
     * test to error without running the test body or {@see tearDown()}.
     *
     * @since 4.0
     *
     * @codeCoverageIgnore
     */
    protected function set_up(): void
    {
    }
    /**
     * Performs assertions shared by all tests in the class, run after setUp() and before the test body.
     *
     * Override this method to assert invariants that must hold before every test
     * executes — for example, verifying that a shared fixture is in a known state.
     * Failures here are reported as assertion failures, not errors.
     *
     * @since 3.5
     *
     * @codeCoverageIgnore
     */
    protected function assert_pre_conditions(): void
    {
    }
    /**
     * Performs assertions shared by all tests in the class, run after the test body and before tearDown().
     *
     * Override this method to assert invariants that must hold after every test
     * executes — for example, verifying that no unexpected output was produced
     * or that a resource was properly released by the code under test.
     * Failures here are reported as assertion failures, not errors.
     *
     * @since 3.5
     *
     * @codeCoverageIgnore
     */
    protected function assert_post_conditions(): void
    {
    }
    /**
     * Called after each individual test method, even if the test threw an exception.
     *
     * Use this hook to release per-test resources allocated in {@see setUp()}
     * (e.g. close file handles, reset singletons, restore global state).
     * Exceptions thrown here are reported separately so the original test
     * failure is not lost.
     *
     * @since 4.0
     *
     * @codeCoverageIgnore
     */
    protected function tear_down(): void
    {
    }
    /**
     * Returns a string representation of the test case.
     *
     * @throws Exception
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function to_string(): string
    {
        $buffer = sprintf('%s::%s', (new ReflectionClass($this))->get_name(), $this->method_name);
        return $buffer . $this->data_set_as_string_with_data();
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function count(): int
    {
        return 1;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function status(): Test_Status
    {
        return $this->status;
    }
    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\Template\InvalidArgumentException
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     * @throws UnintentionallyCoveredCodeException
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function run(): void
    {
        if (!$this->handle_dependencies()) {
            return;
        }
        if (!$this->should_run_in_separate_process() || $this->requirements_not_satisfied()) {
            try {
                Shutdown_Handler::set_message(sprintf('Fatal error: Premature end of PHP process when running %s.', $this->to_string()));
                (new Test_Runner())->run($this);
            } finally {
                Shutdown_Handler::reset_message();
            }
            return;
        }
        Isolated_Test_Runner_Registry::run($this, $this->preserve_global_state, $this->requires_xdebug());
    }
    /**
     * @return list<string>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function groups(): array
    {
        return $this->groups;
    }
    /**
     * @param list<string> $groups
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_groups(array $groups): void
    {
        $this->groups = $groups;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function name_with_data_set(): string
    {
        return $this->method_name . $this->data_set_as_string();
    }
    /**
     * @return non-empty-string
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function name(): string
    {
        return $this->method_name;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function size(): Test_Size
    {
        return (new Groups())->size(static::class, $this->method_name);
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     *
     * @phpstan-assert-if-true non-empty-string $this->output()
     */
    final public function has_unexpected_output(): bool
    {
        if ($this->output === '') {
            return false;
        }
        if ($this->expects_output()) {
            return false;
        }
        return true;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function output(): string
    {
        if (!$this->output_buffering_active) {
            return $this->output;
        }
        return (string) ob_get_contents();
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function does_not_perform_assertions(): bool
    {
        return $this->does_not_perform_assertions;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function expects_output(): bool
    {
        if ($this->has_expectation_on_output()) {
            return true;
        }
        return $this->output_retrieved_for_assertion;
    }
    /**
     * @throws Throwable
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function run_bare(): void
    {
        $emitter = Event\Facade::emitter();
        error_clear_last();
        clearstatcache();
        $emitter->test_preparation_started($this->value_object_for_events());
        $this->snapshot_global_state();
        $this->snapshot_global_error_exception_handlers();
        $this->handle_environment_variables();
        $this->start_output_buffering();
        $hook_methods = (new Hook_Methods())->hook_methods(static::class);
        $has_met_requirements = false;
        $this->number_of_assertions_performed = 0;
        $current_working_directory = getcwd();
        try {
            $this->check_requirements();
            $has_met_requirements = true;
            if ($this->in_isolation) {
                // @codeCoverageIgnoreStart
                $this->invoke_before_class_hook_methods($hook_methods, $emitter);
                // @codeCoverageIgnoreEnd
            }
            if (method_exists(static::class, $this->method_name) && Metadata_Registry::parser()->for_class_and_method(static::class, $this->method_name)->is_does_not_perform_assertions()->is_not_empty()) {
                $this->does_not_perform_assertions = true;
            }
            $this->invoke_before_test_hook_methods($hook_methods, $emitter);
            $this->invoke_pre_condition_hook_methods($hook_methods, $emitter);
            $emitter->test_prepared($this->value_object_for_events());
            $this->was_prepared = true;
            $this->test_result = $this->run_test();
            $this->verify_deprecation_expectations();
            $this->verify_mock_objects();
            $this->invoke_post_condition_hook_methods($hook_methods, $emitter);
            $this->status = Test_Status::success();
        } catch (Incomplete_Test $e) {
            $this->status = Test_Status::incomplete($e->get_message());
            $emitter->test_marked_as_incomplete($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e));
        } catch (Skipped_Test $e) {
            $this->status = Test_Status::skipped($e->get_message());
            $emitter->test_skipped($this->value_object_for_events(), $e->get_message());
        } catch (AssertionError|Assertion_Failed_Error $e) {
            $this->handle_exception_from_invoked_count_mock_object_rule($e);
            if (!$this->was_prepared) {
                $this->was_prepared = true;
                $emitter->test_preparation_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e));
            }
            $this->status = Test_Status::failure($e->get_message());
            $emitter->test_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e), Event\Code\Comparison_Failure_Builder::from($e));
        } catch (Timeout_Exception $e) {
        } catch (Throwable $_e) {
            if ($this->is_registered_failure($_e)) {
                $this->status = Test_Status::failure($_e->get_message());
                $emitter->test_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($_e), null);
            } else {
                $e = $this->transform_exception($_e);
                $this->status = Test_Status::error($e->get_message());
                if (!$this->was_prepared) {
                    if ($e instanceof Assertion_Failed_Error) {
                        $emitter->test_preparation_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e));
                    } else {
                        $emitter->test_preparation_errored($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e));
                    }
                }
                $emitter->test_errored($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e));
            }
        }
        $output_buffering_stopped = false;
        if (!isset($e) && $this->has_expectation_on_output() && $this->stop_output_buffering()) {
            $output_buffering_stopped = true;
            $this->perform_assertions_on_output();
        }
        try {
            $this->mock_objects = [];
            /** @phpstan-ignore catch.neverThrown */
        } catch (Throwable $e) {
            Event\Facade::emitter()->test_errored($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e));
        }
        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            if ($has_met_requirements) {
                $this->invoke_after_test_hook_methods($hook_methods, $emitter);
                if ($this->in_isolation) {
                    // @codeCoverageIgnoreStart
                    $this->invoke_after_class_hook_methods($hook_methods, $emitter);
                    // @codeCoverageIgnoreEnd
                }
            }
        } catch (AssertionError|Assertion_Failed_Error $e) {
            $this->status = Test_Status::failure($e->get_message());
            $emitter->test_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e), Event\Code\Comparison_Failure_Builder::from($e));
        } catch (Throwable $exception_raised_during_tear_down) {
            if (!isset($e) || $e instanceof Skipped_With_Message_Exception) {
                $this->status = Test_Status::error($exception_raised_during_tear_down->get_message());
                $e = $exception_raised_during_tear_down;
                $emitter->test_errored($this->value_object_for_events(), Event\Code\Throwable_Builder::from($exception_raised_during_tear_down));
            }
        }
        if (!isset($e) && !isset($_e)) {
            $emitter->test_passed($this->value_object_for_events());
            if (!$this->uses_data_provider()) {
                Passed_Tests::instance()->test_method_passed($this->value_object_for_events(), $this->test_result);
            }
        }
        if (!$output_buffering_stopped) {
            $this->stop_output_buffering();
        }
        clearstatcache();
        if ($current_working_directory !== false && $current_working_directory !== getcwd()) {
            chdir($current_working_directory);
        }
        $this->restore_environment_variables();
        $this->restore_global_error_exception_handlers();
        $this->restore_global_state();
        $this->unregister_custom_comparators();
        libxml_clear_errors();
        $this->test_value_object_for_events = null;
        if (isset($e)) {
            $this->on_not_successful_test($e);
        }
    }
    /**
     * @param list<ExecutionOrderDependency> $dependencies
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_dependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }
    /**
     * @param array<non-empty-string, array<mixed>> $dependencyInput
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     *
     * @codeCoverageIgnore
     */
    final public function set_dependency_input(array $dependency_input): void
    {
        $this->dependency_input = $dependency_input;
    }
    /**
     * @return array<non-empty-string, array<mixed>>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dependency_input(): array
    {
        return $this->dependency_input;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function has_dependency_input(): bool
    {
        return $this->dependency_input !== [];
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_backup_globals(bool $backup_globals): void
    {
        $this->backup_globals = $backup_globals;
    }
    /**
     * @param list<string> $backupGlobalsExcludeList
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_backup_globals_exclude_list(array $backup_globals_exclude_list): void
    {
        $this->backup_globals_exclude_list = $backup_globals_exclude_list;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_backup_static_properties(bool $backup_static_properties): void
    {
        $this->backup_static_properties = $backup_static_properties;
    }
    /**
     * @param array<string,list<class-string>> $backupStaticPropertiesExcludeList
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_backup_static_properties_exclude_list(array $backup_static_properties_exclude_list): void
    {
        $this->backup_static_properties_exclude_list = $backup_static_properties_exclude_list;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_run_test_in_separate_process(bool $run_test_in_separate_process): void
    {
        if ($this->run_test_in_separate_process === null) {
            $this->run_test_in_separate_process = $run_test_in_separate_process;
        }
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_preserve_global_state(bool $preserve_global_state): void
    {
        $this->preserve_global_state = $preserve_global_state;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     *
     * @codeCoverageIgnore
     */
    final public function set_in_isolation(bool $in_isolation): void
    {
        $this->in_isolation = $in_isolation;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     *
     * @codeCoverageIgnore
     */
    final public function result(): mixed
    {
        return $this->test_result;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_result(mixed $result): void
    {
        $this->test_result = $result;
    }
    /**
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function register_mock_object(string $type, Mock_Object $mock_object): void
    {
        assert($mock_object instanceof Mock_Object_Internal);
        $this->mock_objects[] = ['type' => $type, 'mockObject' => $mock_object];
    }
    /**
     * @param non-negative-int $count
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function add_to_assertion_count(int $count): void
    {
        assert($count >= 0);
        $this->number_of_assertions_performed += $count;
    }
    /**
     * @return non-negative-int
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function number_of_assertions_performed(): int
    {
        return $this->number_of_assertions_performed;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function uses_data_provider(): bool
    {
        return $this->data !== [];
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function data_name(): int|string
    {
        return $this->data_name;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function data_set_as_string(): string
    {
        if ($this->data !== []) {
            if (is_int($this->data_name)) {
                return sprintf(' with data set #%s', $this->data_name);
            }
            return sprintf(' with data set "%s"', $this->data_name);
        }
        return '';
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function data_set_as_string_with_data(): string
    {
        if ($this->data === []) {
            return '';
        }
        return sprintf('%s with data (%s)', $this->data_set_as_filter_string(), Exporter::shortened_recursive_export($this->data));
    }
    /**
     * @return array<mixed>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function provided_data(): array
    {
        return $this->data;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function sort_id(): string
    {
        $id = $this->method_name;
        if (!str_contains($id, '::')) {
            $id = static::class . '::' . $id;
        }
        if ($this->uses_data_provider()) {
            $id .= $this->data_set_as_string();
        }
        return $id;
    }
    /**
     * @return list<ExecutionOrderDependency>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function provides(): array
    {
        return $this->provided_tests;
    }
    /**
     * @return list<ExecutionOrderDependency>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function requires(): array
    {
        return $this->dependencies;
    }
    /**
     * @param array<mixed> $data
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function set_data(int|string $data_name, array $data): void
    {
        $this->data_name = $data_name;
        $this->data = $data;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function value_object_for_events(): Event\Code\Test_Method
    {
        if ($this->test_value_object_for_events !== null) {
            return $this->test_value_object_for_events;
        }
        $this->test_value_object_for_events = Event\Code\Test_Method_Builder::from_test_case($this);
        return $this->test_value_object_for_events;
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function was_prepared(): bool
    {
        return $this->was_prepared;
    }
    /**
     * Returns a matcher that matches when the method is executed zero or more times.
     *
     * Use this only when you genuinely do not care whether the method is called at all.
     * In most cases, a test stub (via {@see createStub()}) or an explicit invocation
     * count expectation is a clearer and safer alternative.
     *
     * @deprecated since PHPUnit 11 — Use a test stub or an explicit count expectation instead.
     *             Will be removed in PHPUnit 14.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/6461
     */
    final protected function any(): Any_Invoked_Count_Matcher
    {
        Event\Facade::emitter()->test_triggered_phpunit_deprecation($this->test_value_object_for_events, 'The any() invoked count expectation is deprecated and will be removed in PHPUnit 14. ' . 'Use a test stub instead or configure a real invocation count expectation.');
        return new Any_Invoked_Count_Matcher();
    }
    /**
     * Returns a matcher that matches when the method is never executed.
     */
    final protected function never(): Invoked_Count_Matcher
    {
        return new Invoked_Count_Matcher(0);
    }
    /**
     * Returns a matcher that matches when the method is executed
     * at least N times.
     */
    final protected function at_least(int $required_invocations): Invoked_At_Least_Count_Matcher
    {
        if ($required_invocations < 1) {
            Event\Facade::emitter()->test_triggered_phpunit_deprecation($this->value_object_for_events(), 'Calling atLeast() with an argument that is not positive is deprecated.' . PHP_EOL . 'This will become an error in PHPUnit 14.');
        }
        return new Invoked_At_Least_Count_Matcher($required_invocations);
    }
    /**
     * Returns a matcher that matches when the method is executed at least once.
     */
    final protected function at_least_once(): Invoked_At_Least_Once_Matcher
    {
        return new Invoked_At_Least_Once_Matcher();
    }
    /**
     * Returns a matcher that matches when the method is executed exactly once.
     */
    final protected function once(): Invoked_Count_Matcher
    {
        return new Invoked_Count_Matcher(1);
    }
    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     */
    final protected function exactly(int $count): Invoked_Count_Matcher
    {
        return new Invoked_Count_Matcher($count);
    }
    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     */
    final protected function at_most(int $allowed_invocations): Invoked_At_Most_Count_Matcher
    {
        return new Invoked_At_Most_Count_Matcher($allowed_invocations);
    }
    final protected function throw_exception(Throwable $exception): Exception_Stub
    {
        return new Exception_Stub($exception);
    }
    final protected function get_actual_output_for_assertion(): string
    {
        $this->output_retrieved_for_assertion = true;
        return $this->output();
    }
    /**
     * Asserts that output produced by the code under test matches a PCRE regular expression.
     *
     * Call this before executing the code that produces output. The assertion is
     * evaluated after the test body and before {@see tearDown()}.
     *
     * @param non-empty-string $expected_regex PCRE pattern including delimiters (e.g. '/^Hello/')
     */
    final protected function expect_output_regex(string $expected_regex): void
    {
        $this->output_expected_regex = $expected_regex;
    }
    /**
     * Asserts that output produced by the code under test equals the given string exactly.
     *
     * Call this before executing the code that produces output. The assertion is
     * evaluated after the test body and before {@see tearDown()}.
     *
     * @param string $expected_string The exact string expected on stdout
     */
    final protected function expect_output_string(string $expected_string): void
    {
        $this->output_expected_string = $expected_string;
    }
    /**
     * Asserts that the code under test writes to the PHP error log.
     *
     * Call this before executing the code. PHPUnit redirects error_log output
     * during the test and verifies it was non-empty.
     */
    final protected function expect_error_log(): void
    {
        $this->expect_error_log = true;
    }
    /**
     * Asserts that the code under test throws an exception of the given class.
     *
     * Must be called before the code that is expected to throw. The test passes
     * only if the exception is thrown; if no exception is thrown the test fails.
     * Combine with {@see expectExceptionMessage()} and {@see expectExceptionCode()}
     * for more precise assertions.
     *
     * @param class-string<Throwable> $exception Fully-qualified exception class name
     */
    final protected function expect_exception(string $exception): void
    {
        $this->expected_exception = $exception;
    }
    /**
     * Asserts that the thrown exception has the given code.
     *
     * Must be called after {@see expectException()} and before the throwing code.
     *
     * @param int|string $code Expected exception code (from Throwable::getCode())
     */
    final protected function expect_exception_code(int|string $code): void
    {
        $this->expected_exception_code = $code;
    }
    /**
     * Asserts that the thrown exception message equals the given string.
     *
     * The comparison checks whether the actual message contains the expected
     * string as a substring. For an exact regex match use
     * {@see expectExceptionMessageMatches()} instead.
     *
     * @param string $message Expected substring of the exception message
     */
    final protected function expect_exception_message(string $message): void
    {
        $this->expected_exception_message = $message;
    }
    /**
     * Asserts that the thrown exception message matches a PCRE regular expression.
     *
     * @param non-empty-string $regular_expression PCRE pattern including delimiters
     */
    final protected function expect_exception_message_matches(string $regular_expression): void
    {
        $this->expected_exception_message_reg_exp = $regular_expression;
    }
    /**
     * Sets up an expectation for an exception to be raised by the code under test.
     *
     * Convenience wrapper that calls {@see expectException()},
     * {@see expectExceptionMessage()}, and {@see expectExceptionCode()} using the
     * class, message, and code of the provided exception object.
     *
     * @param Throwable $exception Template exception whose class, message, and code will be expected
     */
    final protected function expect_exception_object(Throwable $exception): void
    {
        $this->expect_exception($exception::class);
        $this->expect_exception_message($exception->get_message());
        $this->expect_exception_code($exception->get_code());
    }
    /**
     * Marks this test as intentionally not performing any assertions.
     *
     * By default, PHPUnit fails tests that perform zero assertions (the
     * "risky test" check). Call this method in tests that only verify side
     * effects (e.g. that no exception is thrown) to suppress that warning.
     */
    final protected function expect_not_to_perform_assertions(): void
    {
        $this->does_not_perform_assertions = true;
    }
    /**
     * @param non-empty-string $expectedUserDeprecationMessage
     */
    final protected function expect_user_deprecation_message(string $expected_user_deprecation_message): void
    {
        $this->expected_user_deprecation_message[] = $expected_user_deprecation_message;
    }
    /**
     * @param non-empty-string $expectedUserDeprecationMessageRegularExpression
     */
    final protected function expect_user_deprecation_message_matches(string $expected_user_deprecation_message_regular_expression): void
    {
        $this->expected_user_deprecation_message_regular_expression[] = $expected_user_deprecation_message_regular_expression;
    }
    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $className
     *
     * @return MockBuilder<RealInstanceType>
     */
    final protected function get_mock_builder(string $class_name): Mock_Builder
    {
        return new Mock_Builder($this, $class_name);
    }
    final protected function register_comparator(Comparator $comparator): void
    {
        Comparator_Factory::get_instance()->register($comparator);
        Event\Facade::emitter()->test_registered_comparator($comparator::class);
        $this->custom_comparators[] = $comparator;
    }
    /**
     * @param class-string $classOrInterface
     */
    final protected function register_failure_type(string $class_or_interface): void
    {
        $this->failure_types[$class_or_interface] = true;
    }
    /**
     * Creates a mock object for the specified interface or class.
     *
     * The generated mock object:
     *  - Does NOT call the original constructor.
     *  - Does NOT call the original clone method.
     *  - Stubs all methods to return a generated default value (null / 0 / '' etc.)
     *    unless a specific expectation or return-value stub is configured.
     *  - Verifies that all configured call expectations are met at the end of the test.
     *
     * Use {@see createStub()} when you do not need to assert call counts.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type Fully-qualified interface or class name to mock
     *
     * @throws InvalidArgumentException   when $type is not a valid class or interface name
     * @throws MockObjectException        when the mock cannot be generated
     * @throws NoPreviousThrowableException
     *
     * @return MockObject&RealInstanceType A mock that also implements MockObject
     *
     * @since 9.0
     */
    final protected function create_mock(string $type): Mock_Object
    {
        $mock = (new Mock_Generator())->test_double($type, true, callOriginalConstructor: false, callOriginalClone: false, returnValueGeneration: self::generate_return_values_for_test_doubles());
        assert($mock instanceof $type);
        assert($mock instanceof Mock_Object);
        $this->register_mock_object($type, $mock);
        Event\Facade::emitter()->test_created_mock_object($type);
        return $mock;
    }
    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    final protected function create_mock_for_intersection_of_interfaces(array $interfaces): Mock_Object
    {
        $mock = (new Mock_Generator())->test_double_for_interface_intersection($interfaces, true, returnValueGeneration: self::generate_return_values_for_test_doubles());
        assert($mock instanceof Mock_Object);
        $this->register_mock_object(implode('|', $interfaces), $mock);
        Event\Facade::emitter()->test_created_mock_object_for_intersection_of_interfaces($interfaces);
        return $mock;
    }
    /**
     * Creates (and configures) a mock object for the specified interface or class.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param array<non-empty-string, mixed> $configuration
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return MockObject&RealInstanceType
     */
    final protected function create_configured_mock(string $type, array $configuration): Mock_Object
    {
        $o = $this->create_mock($type);
        foreach ($configuration as $method => $return) {
            $o->method($method)->will_return($return);
        }
        return $o;
    }
    /**
     * Creates a partial mock object for the specified interface or class.
     *
     * @param class-string<RealInstanceType> $type
     * @param list<non-empty-string>         $methods
     *
     * @template RealInstanceType of object
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     *
     * @return MockObject&RealInstanceType
     */
    final protected function create_partial_mock(string $type, array $methods): Mock_Object
    {
        $mock_builder = $this->get_mock_builder($type)->disable_original_constructor()->disable_original_clone()->only_methods($methods);
        if (!self::generate_return_values_for_test_doubles()) {
            $mock_builder->disable_auto_return_value_generation();
        }
        $partial_mock = $mock_builder->get_mock();
        Event\Facade::emitter()->test_created_partial_mock_object($type, ...$methods);
        return $partial_mock;
    }
    /**
     * @param non-empty-string $additionalInformation
     */
    final protected function provide_additional_information(string $additional_information): void
    {
        Event\Facade::emitter()->test_provided_additional_information($this->value_object_for_events(), $additional_information);
    }
    protected function transform_exception(Throwable $t): Throwable
    {
        return $t;
    }
    /**
     * This method is called when a test method did not execute successfully.
     *
     * @throws Throwable
     */
    protected function on_not_successful_test(Throwable $t): never
    {
        throw $t;
    }
    /**
     * @param array<mixed> $testArguments
     */
    protected function invoke_test_method(string $method_name, array $test_arguments): mixed
    {
        /** @phpstan-ignore method.dynamicName */
        return $this->{$method_name}(...$test_arguments);
    }
    /**
     * Returns the data set as a string compatible with the --filter CLI option.
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    private function data_set_as_filter_string(): string
    {
        if ($this->data !== []) {
            if (is_int($this->data_name)) {
                return sprintf('#%d', $this->data_name);
            }
            return sprintf('@%s', $this->data_name);
        }
        return '';
    }
    /**
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws Throwable
     */
    private function run_test(): mixed
    {
        $test_arguments = array_merge($this->data, array_values($this->dependency_input));
        $this->start_error_log_capture();
        try {
            $test_result = $this->invoke_test_method($this->method_name, $test_arguments);
            $this->verify_error_log_expectation();
        } catch (Throwable $exception) {
            $this->handle_error_log_error();
            if (!$this->should_exception_expectations_be_verified($exception)) {
                throw $exception;
            }
            $this->verify_exception_expectations($exception);
            return null;
        } finally {
            $this->stop_error_log_capture();
        }
        $this->emit_event_for_custom_test_method_invocation();
        $this->expected_exception_was_not_raised();
        return $test_result;
    }
    private function strip_date_from_error_log(string $log): string
    {
        // https://github.com/php/php-src/blob/c696087e323263e941774ebbf902ac249774ec9f/main/main.c#L905
        $result = preg_replace('/\[\d+-\w+-\d+ \d+:\d+:\d+ [^\r\n[\]]+?\] /', '', $log);
        assert($result !== null);
        return $result;
    }
    /**
     * @throws ExpectationFailedException
     */
    private function verify_deprecation_expectations(): void
    {
        foreach ($this->expected_user_deprecation_message as $deprecation_expectation) {
            $this->number_of_assertions_performed++;
            if (!in_array($deprecation_expectation, Deprecation_Collector::deprecations(), true)) {
                throw new Expectation_Failed_Exception(sprintf('Expected deprecation with message "%s" was not triggered', $deprecation_expectation));
            }
        }
        foreach ($this->expected_user_deprecation_message_regular_expression as $deprecation_expectation) {
            $this->number_of_assertions_performed++;
            $expected_deprecation_triggered = array_any(Deprecation_Collector::deprecations(), static fn(string $deprecation): bool => @preg_match($deprecation_expectation, $deprecation) > 0);
            if (!$expected_deprecation_triggered) {
                throw new Expectation_Failed_Exception(sprintf('Expected deprecation with message matching regular expression "%s" was not triggered', $deprecation_expectation));
            }
        }
    }
    /**
     * @throws Throwable
     */
    private function verify_mock_objects(): void
    {
        $allows_mock_objects_without_expectations = $this->allows_mock_objects_without_expectations();
        $is_phpunit_test_suite = str_starts_with(static::class, 'PHPUnit\\');
        $require_sealed_mock_objects = Configuration_Registry::get()->require_sealed_mock_objects();
        foreach ($this->mock_objects as $mock_object) {
            $mocked_type = $mock_object['type'];
            $mock_object = $mock_object['mockObject'];
            if ($require_sealed_mock_objects && !$mock_object->__phpunit_get_invocation_handler()->is_sealed()) {
                Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), sprintf('Mock object for %s has not been sealed', $mocked_type));
            }
            if (!$mock_object->__phpunit_has_invocation_count_rule()) {
                if (!$mock_object->__phpunit_has_parameters_rule() && !$allows_mock_objects_without_expectations && !$is_phpunit_test_suite) {
                    Event\Facade::emitter()->test_triggered_phpunit_notice($this->test_value_object_for_events, sprintf('No expectations were configured for the mock object for %s. ' . 'Consider refactoring your test code to use a test stub instead. ' . 'The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.', $mocked_type));
                }
                continue;
            }
            $this->number_of_assertions_performed++;
            $mock_object->__phpunit_verify($this->should_invocation_mocker_be_reset($mock_object));
        }
    }
    /**
     * @throws SkippedTest
     */
    private function check_requirements(): void
    {
        if ($this->method_name === '' || !method_exists($this, $this->method_name)) {
            return;
        }
        $missing_requirements = (new Requirements())->requirements_not_satisfied_for(static::class, $this->method_name);
        if ($missing_requirements !== []) {
            $this->mark_test_skipped(implode(PHP_EOL, $missing_requirements));
        }
    }
    private function handle_dependencies(): bool
    {
        if ([] === $this->dependencies || $this->in_isolation) {
            return true;
        }
        $passed_tests = Passed_Tests::instance();
        foreach ($this->dependencies as $dependency) {
            if (!$dependency->is_valid()) {
                $this->mark_error_for_invalid_dependency();
                return false;
            }
            if ($dependency->target_is_class()) {
                $dependency_class_name = $dependency->get_target_class_name();
                if (!class_exists($dependency_class_name)) {
                    $this->mark_error_for_invalid_dependency($dependency);
                    return false;
                }
                if (!$passed_tests->has_test_class_passed($dependency_class_name)) {
                    $this->mark_skipped_for_missing_dependency($dependency);
                    return false;
                }
                continue;
            }
            $dependency_target = $dependency->get_target();
            if (!$passed_tests->has_test_method_passed($dependency_target)) {
                if (!$this->is_callable_test_method($dependency_target)) {
                    $this->mark_error_for_invalid_dependency($dependency);
                } else {
                    $this->mark_skipped_for_missing_dependency($dependency);
                }
                return false;
            }
            if ($passed_tests->is_greater_than($dependency_target, $this->size())) {
                Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), 'This test depends on a test that is larger than itself');
                return true;
            }
            if (!$passed_tests->has_return_value($dependency_target)) {
                return true;
            }
            $return_value = $passed_tests->return_value($dependency_target);
            if ($dependency->deep_clone()) {
                $deep_copy = new Deep_Copy();
                $deep_copy->skip_uncloneable(false);
                $this->dependency_input[$dependency_target] = $deep_copy->copy($return_value);
            } elseif ($dependency->shallow_clone()) {
                $this->dependency_input[$dependency_target] = clone $return_value;
            } else {
                $this->dependency_input[$dependency_target] = $return_value;
            }
        }
        $this->test_value_object_for_events = null;
        return true;
    }
    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    private function mark_error_for_invalid_dependency(?Execution_Order_Dependency $dependency = null): void
    {
        $message = 'This test has an invalid dependency';
        if ($dependency !== null) {
            $message = sprintf('This test depends on "%s" which does not exist', $dependency->target_is_class() ? $dependency->get_target_class_name() : $dependency->get_target());
        }
        $exception = new Invalid_Dependency_Exception($message);
        Event\Facade::emitter()->test_errored($this->value_object_for_events(), Event\Code\Throwable_Builder::from($exception));
        $this->status = Test_Status::error($message);
    }
    private function mark_skipped_for_missing_dependency(Execution_Order_Dependency $dependency): void
    {
        $message = sprintf('This test depends on "%s" to pass', $dependency->get_target());
        Event\Facade::emitter()->test_skipped($this->value_object_for_events(), $message);
        $this->status = Test_Status::skipped($message);
    }
    private function start_output_buffering(): void
    {
        ob_start();
        $this->output_buffering_active = true;
        $this->output_buffering_level = ob_get_level();
    }
    private function stop_output_buffering(): bool
    {
        $buffering_level = ob_get_level();
        if ($buffering_level !== $this->output_buffering_level) {
            if ($buffering_level > $this->output_buffering_level) {
                $message = 'Test code or tested code did not close its own output buffers';
            } else {
                $message = 'Test code or tested code closed output buffers other than its own';
            }
            while (ob_get_level() >= $this->output_buffering_level) {
                ob_end_clean();
            }
            Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), $message);
            return false;
        }
        $this->output = ob_get_clean();
        $this->output_buffering_active = false;
        $this->output_buffering_level = ob_get_level();
        return true;
    }
    private function snapshot_global_error_exception_handlers(): void
    {
        $this->backup_global_error_handlers = $this->active_error_handlers();
        $this->backup_global_exception_handlers = $this->active_exception_handlers();
    }
    private function restore_global_error_exception_handlers(): void
    {
        $active_error_handlers = $this->active_error_handlers();
        $active_exception_handlers = $this->active_exception_handlers();
        $message = null;
        if ($active_error_handlers !== $this->backup_global_error_handlers) {
            if (count($active_error_handlers) > count($this->backup_global_error_handlers)) {
                if (!$this->in_isolation) {
                    $message = 'Test code or tested code did not remove its own error handlers';
                }
            } else {
                $message = 'Test code or tested code removed error handlers other than its own';
            }
            foreach ($active_error_handlers as $handler) {
                restore_error_handler();
            }
            foreach ($this->backup_global_error_handlers as $handler) {
                set_error_handler($handler);
            }
        }
        if ($message !== null) {
            Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), $message);
        }
        $message = null;
        if ($active_exception_handlers !== $this->backup_global_exception_handlers) {
            if (count($active_exception_handlers) > count($this->backup_global_exception_handlers)) {
                if (!$this->in_isolation) {
                    $message = 'Test code or tested code did not remove its own exception handlers';
                }
            } else {
                $message = 'Test code or tested code removed exception handlers other than its own';
            }
            foreach ($active_exception_handlers as $handler) {
                restore_exception_handler();
            }
            foreach ($this->backup_global_exception_handlers as $handler) {
                set_exception_handler($handler);
            }
        }
        $this->backup_global_error_handlers = null;
        $this->backup_global_exception_handlers = null;
        if ($message !== null) {
            Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), $message);
        }
    }
    /**
     * @return list<callable>
     */
    private function active_error_handlers(): array
    {
        $active_error_handlers = [];
        while (true) {
            $previous_handler = set_error_handler(static fn(): false => false);
            restore_error_handler();
            if ($previous_handler === null) {
                break;
            }
            $active_error_handlers[] = $previous_handler;
            restore_error_handler();
        }
        $active_error_handlers = array_reverse($active_error_handlers);
        $invalid_error_handler_stack = false;
        foreach ($active_error_handlers as $handler) {
            if (!is_callable($handler)) {
                $invalid_error_handler_stack = true;
                continue;
            }
            set_error_handler($handler);
        }
        if ($invalid_error_handler_stack) {
            $message = 'At least one error handler is not callable outside the scope it was registered in';
            Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), $message);
        }
        return $active_error_handlers;
    }
    /**
     * @return list<callable>
     */
    private function active_exception_handlers(): array
    {
        $res = [];
        while (true) {
            $previous_handler = set_exception_handler(static fn(): null => null);
            restore_exception_handler();
            if ($previous_handler === null) {
                break;
            }
            $res[] = $previous_handler;
            restore_exception_handler();
        }
        $res = array_reverse($res);
        foreach ($res as $handler) {
            set_exception_handler($handler);
        }
        return $res;
    }
    private function snapshot_global_state(): void
    {
        if ($this->run_test_in_separate_process || $this->in_isolation || !$this->backup_globals && !$this->backup_static_properties) {
            return;
        }
        $snapshot = $this->create_global_state_snapshot($this->backup_globals === true);
        $this->snapshot = $snapshot;
    }
    private function restore_global_state(): void
    {
        if (!$this->snapshot instanceof Snapshot) {
            return;
        }
        if (Configuration_Registry::get()->be_strict_about_changes_to_global_state()) {
            $this->compare_global_state_snapshots($this->snapshot, $this->create_global_state_snapshot($this->backup_globals === true));
        }
        $restorer = new Restorer();
        if ($this->backup_globals) {
            $restorer->restore_global_variables($this->snapshot);
        }
        if ($this->backup_static_properties) {
            $restorer->restore_static_properties($this->snapshot);
        }
        $this->snapshot = null;
    }
    private function create_global_state_snapshot(bool $backup_globals): Snapshot
    {
        $exclude_list = new Global_State_Exclude_List();
        foreach ($this->backup_globals_exclude_list as $global_variable) {
            $exclude_list->add_global_variable($global_variable);
        }
        if (!defined('PHPUNIT_TESTSUITE')) {
            $exclude_list->add_class_name_prefix('PHPUnit');
            $exclude_list->add_class_name_prefix('SebastianBergmann\CodeCoverage');
            $exclude_list->add_class_name_prefix('SebastianBergmann\FileIterator');
            $exclude_list->add_class_name_prefix('SebastianBergmann\Invoker');
            $exclude_list->add_class_name_prefix('SebastianBergmann\Template');
            $exclude_list->add_class_name_prefix('SebastianBergmann\Timer');
            $exclude_list->add_static_property(Comparator_Factory::class, 'instance');
            foreach ($this->backup_static_properties_exclude_list as $class => $properties) {
                foreach ($properties as $property) {
                    $exclude_list->add_static_property($class, $property);
                }
            }
        }
        try {
            return new Snapshot($exclude_list, $backup_globals, (bool) $this->backup_static_properties, false, false, false, false, false, false, false);
        } catch (Throwable $t) {
            Event\Facade::emitter()->test_preparation_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($t));
            Event\Facade::emitter()->test_errored($this->value_object_for_events(), Event\Code\Throwable_Builder::from($t));
            throw $t;
        }
    }
    private function compare_global_state_snapshots(Snapshot $before, Snapshot $after): void
    {
        $backup_globals = $this->backup_globals === null || $this->backup_globals;
        if ($backup_globals) {
            $this->compare_global_state_snapshot_part($before->global_variables(), $after->global_variables(), "--- Global variables before the test\n+++ Global variables after the test\n");
            $this->compare_global_state_snapshot_part($before->super_global_variables(), $after->super_global_variables(), "--- Super-global variables before the test\n+++ Super-global variables after the test\n");
        }
        if ($this->backup_static_properties) {
            $this->compare_global_state_snapshot_part($before->static_properties(), $after->static_properties(), "--- Static properties before the test\n+++ Static properties after the test\n");
        }
    }
    /**
     * @param array<mixed> $before
     * @param array<mixed> $after
     */
    private function compare_global_state_snapshot_part(array $before, array $after, string $header): void
    {
        if ($before === $after) {
            return;
        }
        $differ = new Differ(new Unified_Diff_Output_Builder($header));
        Event\Facade::emitter()->test_considered_risky($this->value_object_for_events(), 'This test modified global state but was not expected to do so' . PHP_EOL . trim($differ->diff(Exporter::export($before), Exporter::export($after))));
    }
    private function handle_environment_variables(): void
    {
        $with_environment_variables = Metadata_Registry::parser()->for_class_and_method(static::class, $this->method_name)->is_with_environment_variable();
        $environment_variables = [];
        foreach ($with_environment_variables as $metadata) {
            assert($metadata instanceof With_Environment_Variable);
            $environment_variables[$metadata->environment_variable_name()] = $metadata->value();
        }
        foreach ($environment_variables as $environment_variable_name => $environment_variable_value) {
            $this->backup_environment_variables = [...$this->backup_environment_variables, ...Backed_Up_Environment_Variable::create($environment_variable_name)];
            if ($environment_variable_value === null) {
                unset($_ENV[$environment_variable_name]);
                putenv($environment_variable_name);
            } else {
                $_ENV[$environment_variable_name] = $environment_variable_value;
                putenv("{$environment_variable_name}={$environment_variable_value}");
            }
        }
    }
    private function restore_environment_variables(): void
    {
        foreach ($this->backup_environment_variables as $backup_environment_variable) {
            $backup_environment_variable->restore();
        }
        $this->backup_environment_variables = [];
    }
    private function should_invocation_mocker_be_reset(Mock_Object $mock): bool
    {
        $enumerator = new Enumerator();
        if (in_array($mock, $enumerator->enumerate($this->dependency_input), true)) {
            return false;
        }
        if (!is_array($this->test_result) && !is_object($this->test_result)) {
            return true;
        }
        return !in_array($mock, $enumerator->enumerate($this->test_result), true);
    }
    private function unregister_custom_comparators(): void
    {
        $factory = Comparator_Factory::get_instance();
        foreach ($this->custom_comparators as $comparator) {
            $factory->unregister($comparator);
        }
        $this->custom_comparators = [];
    }
    /**
     * @throws Exception
     */
    private function should_exception_expectations_be_verified(Throwable $throwable): bool
    {
        $result = false;
        if ($this->expected_exception !== null || $this->expected_exception_code !== null || $this->expected_exception_message !== null || $this->expected_exception_message_reg_exp !== null) {
            $result = true;
        }
        if ($throwable instanceof Exception) {
            $result = false;
        }
        if (is_string($this->expected_exception)) {
            try {
                $reflector = new ReflectionClass($this->expected_exception);
                // @codeCoverageIgnoreStart
            } catch (Reflection_Exception $e) {
                throw new Exception($e->get_message(), $e->get_code(), $e);
            }
            // @codeCoverageIgnoreEnd
            if ($this->expected_exception === \Php_Unit\Framework\Exception::class || $this->expected_exception === \Php_Unit\Framework\Exception::class || $reflector->is_subclass_of(Exception::class)) {
                $result = true;
            }
        }
        return $result;
    }
    private function should_run_in_separate_process(): bool
    {
        if ($this->in_isolation) {
            return false;
        }
        if ($this->run_test_in_separate_process) {
            return true;
        }
        return Configuration_Registry::get()->process_isolation();
    }
    private function is_callable_test_method(string $dependency): bool
    {
        [$class_name, $method_name] = explode('::', $dependency);
        if (!class_exists($class_name)) {
            return false;
        }
        $class = new ReflectionClass($class_name);
        if (!$class->is_subclass_of(self::class)) {
            return false;
        }
        if (!$class->has_method($method_name)) {
            return false;
        }
        return Test_Util::is_test_method($class->get_method($method_name));
    }
    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws NoPreviousThrowableException
     */
    private function perform_assertions_on_output(): void
    {
        try {
            if ($this->output_expected_regex !== null) {
                $this->assert_matches_regular_expression($this->output_expected_regex, $this->output);
            } elseif ($this->output_expected_string !== null) {
                $this->assert_same($this->output_expected_string, $this->output);
            }
        } catch (Expectation_Failed_Exception $e) {
            $this->status = Test_Status::failure($e->get_message());
            Event\Facade::emitter()->test_failed($this->value_object_for_events(), Event\Code\Throwable_Builder::from($e), Event\Code\Comparison_Failure_Builder::from($e));
            throw $e;
        }
    }
    /**
     * @param array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection} $hookMethods
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    private function invoke_before_class_hook_methods(array $hook_methods, Event\Emitter $emitter): void
    {
        $this->invoke_hook_methods($hook_methods['beforeClass'], $emitter, 'beforeFirstTestMethodCalled', 'beforeFirstTestMethodErrored', 'beforeFirstTestMethodFailed', 'beforeFirstTestMethodFinished', false);
    }
    /**
     * @param array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection} $hookMethods
     *
     * @throws Throwable
     */
    private function invoke_before_test_hook_methods(array $hook_methods, Event\Emitter $emitter): void
    {
        $this->invoke_hook_methods($hook_methods['before'], $emitter, 'beforeTestMethodCalled', 'beforeTestMethodErrored', 'beforeTestMethodFailed', 'beforeTestMethodFinished');
    }
    /**
     * @param array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection} $hookMethods
     *
     * @throws Throwable
     */
    private function invoke_pre_condition_hook_methods(array $hook_methods, Event\Emitter $emitter): void
    {
        $this->invoke_hook_methods($hook_methods['preCondition'], $emitter, 'preConditionCalled', 'preConditionErrored', 'preConditionFailed', 'preConditionFinished');
    }
    /**
     * @param array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection} $hookMethods
     *
     * @throws Throwable
     */
    private function invoke_post_condition_hook_methods(array $hook_methods, Event\Emitter $emitter): void
    {
        $this->invoke_hook_methods($hook_methods['postCondition'], $emitter, 'postConditionCalled', 'postConditionErrored', 'postConditionFailed', 'postConditionFinished');
    }
    /**
     * @param array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection} $hookMethods
     *
     * @throws Throwable
     */
    private function invoke_after_test_hook_methods(array $hook_methods, Event\Emitter $emitter): void
    {
        $this->invoke_hook_methods($hook_methods['after'], $emitter, 'afterTestMethodCalled', 'afterTestMethodErrored', 'afterTestMethodFailed', 'afterTestMethodFinished');
    }
    /**
     * @param array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection} $hookMethods
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    private function invoke_after_class_hook_methods(array $hook_methods, Event\Emitter $emitter): void
    {
        $this->invoke_hook_methods($hook_methods['afterClass'], $emitter, 'afterLastTestMethodCalled', 'afterLastTestMethodErrored', 'afterLastTestMethodFailed', 'afterLastTestMethodFinished', false);
    }
    /**
     * @param 'afterLastTestMethodCalled'|'afterTestMethodCalled'|'beforeFirstTestMethodCalled'|'beforeTestMethodCalled'|'postConditionCalled'|'preConditionCalled'             $calledMethod
     * @param 'afterLastTestMethodErrored'|'afterTestMethodErrored'|'beforeFirstTestMethodErrored'|'beforeTestMethodErrored'|'postConditionErrored'|'preConditionErrored'       $erroredMethod
     * @param 'afterLastTestMethodFailed'|'afterTestMethodFailed'|'beforeFirstTestMethodFailed'|'beforeTestMethodFailed'|'postConditionFailed'|'preConditionFailed'             $failedMethod
     * @param 'afterLastTestMethodFinished'|'afterTestMethodFinished'|'beforeFirstTestMethodFinished'|'beforeTestMethodFinished'|'postConditionFinished'|'preConditionFinished' $finishedMethod
     *
     * @throws Throwable
     */
    private function invoke_hook_methods(Hook_Method_Collection $hook_methods, Event\Emitter $emitter, string $called_method, string $errored_method, string $failed_method, string $finished_method, bool $for_test_case = true): void
    {
        if ($for_test_case) {
            $test = $this->value_object_for_events();
        } else {
            $test = static::class;
        }
        $methods_invoked = [];
        foreach ($hook_methods->method_names_sorted_by_priority() as $method_name) {
            if ($this->method_does_not_exist_or_is_declared_in_test_case($method_name)) {
                continue;
            }
            $method_invoked = new Event\Code\Class_Method(static::class, $method_name);
            try {
                /** @phpstan-ignore method.dynamicName */
                $this->{$method_name}();
            } catch (Throwable $t) {
            }
            /** @phpstan-ignore method.dynamicName */
            $emitter->{$called_method}($test, $method_invoked);
            $methods_invoked[] = $method_invoked;
            if (isset($t) && !$t instanceof Skipped_Test) {
                if ($t instanceof Assertion_Failed_Error) {
                    $method = $failed_method;
                } else {
                    $method = $errored_method;
                }
                /** @phpstan-ignore method.dynamicName */
                $emitter->{$method}($test, $method_invoked, Event\Code\Throwable_Builder::from($t));
                break;
            }
        }
        if ($methods_invoked !== []) {
            /** @phpstan-ignore method.dynamicName */
            $emitter->{$finished_method}($test, ...$methods_invoked);
        }
        if (isset($t)) {
            throw $t;
        }
    }
    /**
     * @param non-empty-string $methodName
     */
    private function method_does_not_exist_or_is_declared_in_test_case(string $method_name): bool
    {
        $reflector = new Reflection_Object($this);
        if (!$reflector->has_method($method_name)) {
            return true;
        }
        return $reflector->get_method($method_name)->get_declaring_class()->get_name() === self::class;
    }
    /**
     * @throws ExpectationFailedException
     */
    private function verify_exception_expectations(\Exception|Throwable $exception): void
    {
        if ($this->expected_exception !== null) {
            $this->assert_that($exception, new Exception_Constraint($this->expected_exception));
        }
        if ($this->expected_exception_message !== null) {
            $this->assert_that($exception->get_message(), new Exception_Message_Is_Or_Contains($this->expected_exception_message));
        }
        if ($this->expected_exception_message_reg_exp !== null) {
            $this->assert_that($exception->get_message(), new Exception_Message_Matches_Regular_Expression($this->expected_exception_message_reg_exp));
        }
        if ($this->expected_exception_code !== null) {
            $this->assert_that($exception->get_code(), new Exception_Code($this->expected_exception_code));
        }
    }
    /**
     * @throws AssertionFailedError
     */
    private function expected_exception_was_not_raised(): void
    {
        if ($this->expected_exception !== null) {
            $this->assert_that(null, new Exception_Constraint($this->expected_exception));
        } elseif ($this->expected_exception_message !== null) {
            $this->number_of_assertions_performed++;
            throw new Assertion_Failed_Error(sprintf('Failed asserting that exception with message "%s" is thrown', $this->expected_exception_message));
        } elseif ($this->expected_exception_message_reg_exp !== null) {
            $this->number_of_assertions_performed++;
            throw new Assertion_Failed_Error(sprintf('Failed asserting that exception with message matching "%s" is thrown', $this->expected_exception_message_reg_exp));
        } elseif ($this->expected_exception_code !== null) {
            $this->number_of_assertions_performed++;
            throw new Assertion_Failed_Error(sprintf('Failed asserting that exception with code "%s" is thrown', $this->expected_exception_code));
        }
    }
    private function is_registered_failure(Throwable $t): bool
    {
        return array_any(array_keys($this->failure_types), static fn(string $failure_type): bool => $t instanceof $failure_type);
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    private function has_expectation_on_output(): bool
    {
        return is_string($this->output_expected_string) || is_string($this->output_expected_regex);
    }
    private function requirements_not_satisfied(): bool
    {
        return (new Requirements())->requirements_not_satisfied_for(static::class, $this->method_name) !== [];
    }
    private function requires_xdebug(): bool
    {
        return (new Requirements())->requires_xdebug(static::class, $this->method_name);
    }
    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/6095
     */
    private function handle_exception_from_invoked_count_mock_object_rule(Throwable $t): void
    {
        if (!$t instanceof Expectation_Failed_Exception) {
            return;
        }
        $trace = $t->get_trace();
        if (isset($trace[0]['class']) && $trace[0]['class'] === Invoked_Count::class) {
            $this->number_of_assertions_performed++;
        }
    }
    private function start_error_log_capture(): void
    {
        if (ini_get('display_errors') === '0') {
            Shutdown_Handler::set_message('Fatal error: Premature end of PHPUnit\'s PHP process. Use display_errors=On to see the error message.');
        }
        $error_log_capture = tmpfile();
        if ($error_log_capture === false) {
            return;
        }
        $capture_path = stream_get_meta_data($error_log_capture)['uri'];
        if (!@is_writable($capture_path)) {
            return;
        }
        $this->error_log_capture = $error_log_capture;
        $this->previous_error_log_target = ini_set('error_log', $capture_path);
    }
    /**
     * @throws ErrorLogNotWritableException
     */
    private function verify_error_log_expectation(): void
    {
        if ($this->error_log_capture === false) {
            if ($this->expect_error_log) {
                throw new Error_Log_Not_Writable_Exception();
            }
            return;
        }
        $error_log_output = stream_get_contents($this->error_log_capture);
        if ($this->expect_error_log) {
            $this->assert_not_empty($error_log_output, 'error_log() was not called');
            return;
        }
        if ($error_log_output === false) {
            return;
        }
        print $this->strip_date_from_error_log($error_log_output);
    }
    private function handle_error_log_error(): void
    {
        if ($this->error_log_capture === false) {
            return;
        }
        if ($this->expect_error_log) {
            return;
        }
        $error_log_output = stream_get_contents($this->error_log_capture);
        if ($error_log_output !== false) {
            print $this->strip_date_from_error_log($error_log_output);
        }
    }
    private function stop_error_log_capture(): void
    {
        if ($this->error_log_capture === false) {
            return;
        }
        Shutdown_Handler::reset_message();
        fclose($this->error_log_capture);
        $this->error_log_capture = false;
        if ($this->previous_error_log_target === false) {
            return;
        }
        ini_set('error_log', $this->previous_error_log_target);
        $this->previous_error_log_target = false;
    }
    private function allows_mock_objects_without_expectations(): bool
    {
        return Metadata_Registry::parser()->for_class_and_method(static::class, $this->method_name)->is_allow_mock_objects_without_expectations()->is_not_empty();
    }
    private function emit_event_for_custom_test_method_invocation(): void
    {
        $reflector = new ReflectionMethod($this, 'invokeTestMethod');
        if (self::class === $reflector->get_declaring_class()->get_name()) {
            return;
        }
        Event\Facade::emitter()->test_used_custom_method_invocation($this->value_object_for_events(), new Event\Code\Class_Method($reflector->get_declaring_class()->get_name(), 'invokeTestMethod'));
    }
    /**
     * Returns a builder object to create test stubs using a fluent interface.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $className
     *
     * @return TestStubBuilder<RealInstanceType>
     */
    final protected static function get_stub_builder(string $class_name): Test_Stub_Builder
    {
        return new Test_Stub_Builder($class_name);
    }
    /**
     * Creates a test stub for the specified interface or class.
     *
     * A stub differs from a mock in that it does NOT track or verify call counts.
     * Use a stub when you need a collaborator that returns specific values but you
     * do not want to assert that particular methods were called.
     *
     * The generated stub:
     *  - Does NOT call the original constructor.
     *  - Does NOT call the original clone method.
     *  - Returns generated default values (null / 0 / '' etc.) unless configured
     *    with method()->willReturn(...).
     *
     * Use {@see createMock()} when you need to assert call expectations.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type Fully-qualified interface or class name to stub
     *
     * @throws InvalidArgumentException   when $type is not a valid class or interface name
     * @throws MockObjectException        when the stub cannot be generated
     * @throws NoPreviousThrowableException
     *
     * @return RealInstanceType&Stub A stub that also implements the target interface/class
     *
     * @since 9.3
     */
    final protected static function create_stub(string $type): Stub
    {
        $stub = (new Mock_Generator())->test_double($type, false, callOriginalConstructor: false, callOriginalClone: false, returnValueGeneration: self::generate_return_values_for_test_doubles());
        Event\Facade::emitter()->test_created_stub($type);
        assert($stub instanceof $type);
        assert($stub instanceof Stub);
        return $stub;
    }
    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    final protected static function create_stub_for_intersection_of_interfaces(array $interfaces): Stub
    {
        $stub = (new Mock_Generator())->test_double_for_interface_intersection($interfaces, false, returnValueGeneration: self::generate_return_values_for_test_doubles());
        Event\Facade::emitter()->test_created_stub_for_intersection_of_interfaces($interfaces);
        return $stub;
    }
    /**
     * Creates (and configures) a test stub for the specified interface or class.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param array<non-empty-string, mixed> $configuration
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return RealInstanceType&Stub
     */
    final protected static function create_configured_stub(string $type, array $configuration): Stub
    {
        $o = self::create_stub($type);
        foreach ($configuration as $method => $return) {
            $o->method($method)->will_return($return);
        }
        return $o;
    }
    private static function generate_return_values_for_test_doubles(): bool
    {
        return Metadata_Registry::parser()->for_class(static::class)->is_disable_return_value_generation_for_test_doubles()->is_empty();
    }
}