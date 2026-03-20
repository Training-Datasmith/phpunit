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
namespace Php_Unit\Event;

use function assert;
use function memory_reset_peak_usage;
use Php_Unit\Event\Code\Class_Method;
use Php_Unit\Event\Code\Comparison_Failure;
use Php_Unit\Event\Code\Issue_Trigger\Issue_Trigger;
use Php_Unit\Event\Code\No_Test_Case_Object_On_Call_Stack_Exception;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Code\Test_Method_Builder;
use Php_Unit\Event\Code\Throwable;
use Php_Unit\Event\Test\Data_Provider_Method_Called;
use Php_Unit\Event\Test\Data_Provider_Method_Finished;
use Php_Unit\Event\Test_Suite\Filtered as TestSuiteFiltered;
use Php_Unit\Event\Test_Suite\Finished as TestSuiteFinished;
use Php_Unit\Event\Test_Suite\Loaded as TestSuiteLoaded;
use Php_Unit\Event\Test_Suite\Skipped as TestSuiteSkipped;
use Php_Unit\Event\Test_Suite\Sorted as TestSuiteSorted;
use Php_Unit\Event\Test_Suite\Started as TestSuiteStarted;
use Php_Unit\Event\Test_Suite\Test_Suite;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\Ignore_Phpunit_Warnings;
use Php_Unit\Metadata\Parser\Registry;
use Php_Unit\Text_Ui\Configuration\Configuration;
use function preg_match;
use Sebastian_Bergmann\Comparator\Comparator;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Dispatching_Emitter implements Emitter
{
    private readonly Telemetry\Snapshot $start_snapshot;
    private Telemetry\Snapshot $previous_snapshot;
    public function __construct(private readonly Dispatcher $dispatcher, private readonly Telemetry\System $system)
    {
        $this->start_snapshot = $this->system->snapshot();
        $this->previous_snapshot = $this->start_snapshot;
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function application_started(): void
    {
        $this->dispatcher->dispatch(new Application\Started($this->telemetry_info(), new Runtime\Runtime()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_started(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Started($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_configured(Configuration $configuration): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Configured($this->telemetry_info(), $configuration));
    }
    /**
     * @param non-empty-string $filename
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_bootstrap_finished(string $filename): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Bootstrap_Finished($this->telemetry_info(), $filename));
    }
    /**
     * @param non-empty-string $filename
     * @param non-empty-string $name
     * @param non-empty-string $version
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_loaded_extension_from_phar(string $filename, string $name, string $version): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Extension_Loaded_From_Phar($this->telemetry_info(), $filename, $name, $version));
    }
    /**
     * @param class-string          $className
     * @param array<string, string> $parameters
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_bootstrapped_extension(string $class_name, array $parameters): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Extension_Bootstrapped($this->telemetry_info(), $class_name, $parameters));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function data_provider_method_called(Class_Method $test_method, Class_Method $data_provider_method): void
    {
        $this->dispatcher->dispatch(new Data_Provider_Method_Called($this->telemetry_info(), $test_method, $data_provider_method));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function data_provider_method_finished(Class_Method $test_method, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Data_Provider_Method_Finished($this->telemetry_info(), $test_method, ...$called_methods));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_suite_loaded(Test_Suite $test_suite): void
    {
        $this->dispatcher->dispatch(new Test_Suite_Loaded($this->telemetry_info(), $test_suite));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_suite_filtered(Test_Suite $test_suite): void
    {
        $this->dispatcher->dispatch(new Test_Suite_Filtered($this->telemetry_info(), $test_suite));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_suite_sorted(int $execution_order, int $execution_order_defects, bool $resolve_dependencies): void
    {
        $this->dispatcher->dispatch(new Test_Suite_Sorted($this->telemetry_info(), $execution_order, $execution_order_defects, $resolve_dependencies));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_event_facade_sealed(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Event_Facade_Sealed($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_execution_started(Test_Suite $test_suite): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Execution_Started($this->telemetry_info(), $test_suite));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_disabled_garbage_collection(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Garbage_Collection_Disabled($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_triggered_garbage_collection(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Garbage_Collection_Triggered($this->telemetry_info()));
    }
    public function child_process_started(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Child_Process_Started($this->telemetry_info()));
    }
    public function child_process_errored(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Child_Process_Errored($this->telemetry_info()));
    }
    public function child_process_finished(string $stdout, string $stderr): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Child_Process_Finished($this->telemetry_info(), $stdout, $stderr));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_suite_skipped(Test_Suite $test_suite, string $message): void
    {
        $this->dispatcher->dispatch(new Test_Suite_Skipped($this->telemetry_info(), $test_suite, $message));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_suite_started(Test_Suite $test_suite): void
    {
        $this->dispatcher->dispatch(new Test_Suite_Started($this->telemetry_info(), $test_suite));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_preparation_started(Code\Test $test): void
    {
        $this->dispatcher->dispatch(new Test\Preparation_Started($this->telemetry_info(), $test));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_preparation_errored(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Preparation_Errored($this->telemetry_info(), $test, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_preparation_failed(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Preparation_Failed($this->telemetry_info(), $test, $throwable));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_first_test_method_called(string $test_class_name, Class_Method $called_method): void
    {
        $this->dispatcher->dispatch(new Test\Before_First_Test_Method_Called($this->telemetry_info(), $test_class_name, $called_method));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_first_test_method_errored(string $test_class_name, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Before_First_Test_Method_Errored($this->telemetry_info(), $test_class_name, $called_method, $throwable));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_first_test_method_failed(string $test_class_name, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Before_First_Test_Method_Failed($this->telemetry_info(), $test_class_name, $called_method, $throwable));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_first_test_method_finished(string $test_class_name, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Test\Before_First_Test_Method_Finished($this->telemetry_info(), $test_class_name, ...$called_methods));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_test_method_called(Test_Method $test, Class_Method $called_method): void
    {
        $this->dispatcher->dispatch(new Test\Before_Test_Method_Called($this->telemetry_info(), $test, $called_method));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_test_method_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Before_Test_Method_Errored($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_test_method_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Before_Test_Method_Failed($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function before_test_method_finished(Test_Method $test, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Test\Before_Test_Method_Finished($this->telemetry_info(), $test, ...$called_methods));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function pre_condition_called(Test_Method $test, Class_Method $called_method): void
    {
        $this->dispatcher->dispatch(new Test\Pre_Condition_Called($this->telemetry_info(), $test, $called_method));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function pre_condition_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Pre_Condition_Errored($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function pre_condition_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Pre_Condition_Failed($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function pre_condition_finished(Test_Method $test, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Test\Pre_Condition_Finished($this->telemetry_info(), $test, ...$called_methods));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_prepared(Code\Test $test): void
    {
        memory_reset_peak_usage();
        $this->dispatcher->dispatch(new Test\Prepared($this->telemetry_info(), $test));
    }
    /**
     * @param class-string<Comparator> $className
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_registered_comparator(string $class_name): void
    {
        $this->dispatcher->dispatch(new Test\Comparator_Registered($this->telemetry_info(), $class_name));
    }
    public function test_used_custom_method_invocation(Test_Method $test, Class_Method $custom_test_method_invocation): void
    {
        $this->dispatcher->dispatch(new Test\Custom_Test_Method_Invocation_Used($this->telemetry_info(), $test, $custom_test_method_invocation));
    }
    /**
     * @param class-string $className
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_created_mock_object(string $class_name): void
    {
        $this->dispatcher->dispatch(new Test\Mock_Object_Created($this->telemetry_info(), $class_name));
    }
    /**
     * @param list<class-string> $interfaces
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_created_mock_object_for_intersection_of_interfaces(array $interfaces): void
    {
        $this->dispatcher->dispatch(new Test\Mock_Object_For_Intersection_Of_Interfaces_Created($this->telemetry_info(), $interfaces));
    }
    /**
     * @param class-string $className
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_created_partial_mock_object(string $class_name, string ...$method_names): void
    {
        $this->dispatcher->dispatch(new Test\Partial_Mock_Object_Created($this->telemetry_info(), $class_name, ...$method_names));
    }
    /**
     * @param class-string $className
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_created_stub(string $class_name): void
    {
        $this->dispatcher->dispatch(new Test\Test_Stub_Created($this->telemetry_info(), $class_name));
    }
    /**
     * @param list<class-string> $interfaces
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_created_stub_for_intersection_of_interfaces(array $interfaces): void
    {
        $this->dispatcher->dispatch(new Test\Test_Stub_For_Intersection_Of_Interfaces_Created($this->telemetry_info(), $interfaces));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_errored(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Errored($this->telemetry_info(), $test, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_failed(Code\Test $test, Throwable $throwable, ?Comparison_Failure $comparison_failure): void
    {
        $this->dispatcher->dispatch(new Test\Failed($this->telemetry_info(), $test, $throwable, $comparison_failure));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_passed(Code\Test $test): void
    {
        $this->dispatcher->dispatch(new Test\Passed($this->telemetry_info(), $test));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_considered_risky(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\Considered_Risky($this->telemetry_info(), $test, $message));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_marked_as_incomplete(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Marked_Incomplete($this->telemetry_info(), $test, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_skipped(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\Skipped($this->telemetry_info(), $test, $message));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws NoTestCaseObjectOnCallStackException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_phpunit_deprecation(?Code\Test $test, string $message): void
    {
        if ($test === null) {
            $test = Test_Method_Builder::from_call_stack();
        }
        if ($test->is_test_method()) {
            assert($test instanceof Test_Method);
            if ($test->metadata()->is_ignore_phpunit_deprecations()->is_not_empty()) {
                return;
            }
        }
        $this->dispatcher->dispatch(new Test\Phpunit_Deprecation_Triggered($this->telemetry_info(), $test, $message));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws NoTestCaseObjectOnCallStackException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_phpunit_notice(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\Phpunit_Notice_Triggered($this->telemetry_info(), $test, $message));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_php_deprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline, bool $ignored_by_test, Issue_Trigger $trigger): void
    {
        $this->dispatcher->dispatch(new Test\Php_Deprecation_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed, $ignored_by_baseline, $ignored_by_test, $trigger));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $stackTrace
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_deprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline, bool $ignored_by_test, Issue_Trigger $trigger, string $stack_trace): void
    {
        $this->dispatcher->dispatch(new Test\Deprecation_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed, $ignored_by_baseline, $ignored_by_test, $trigger, $stack_trace));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_error(Code\Test $test, string $message, string $file, int $line, bool $suppressed): void
    {
        $this->dispatcher->dispatch(new Test\Error_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_notice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void
    {
        $this->dispatcher->dispatch(new Test\Notice_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed, $ignored_by_baseline));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_php_notice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void
    {
        $this->dispatcher->dispatch(new Test\Php_Notice_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed, $ignored_by_baseline));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_warning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void
    {
        $this->dispatcher->dispatch(new Test\Warning_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed, $ignored_by_baseline));
    }
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_php_warning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void
    {
        $this->dispatcher->dispatch(new Test\Php_Warning_Triggered($this->telemetry_info(), $test, $message, $file, $line, $suppressed, $ignored_by_baseline));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_phpunit_error(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\Phpunit_Error_Triggered($this->telemetry_info(), $test, $message));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_triggered_phpunit_warning(Code\Test $test, string $message): void
    {
        $ignored_by_test = false;
        if ($test->is_test_method()) {
            assert($test instanceof Test_Method);
            $metadata = Registry::parser()->for_method($test->class_name(), $test->method_name())->is_ignore_phpunit_warnings()->as_array();
            if (isset($metadata[0])) {
                assert($metadata[0] instanceof Ignore_Phpunit_Warnings);
                $message_pattern = $metadata[0]->message_pattern();
                if ($message_pattern === null || (bool) preg_match('{' . $message_pattern . '}', $message)) {
                    $ignored_by_test = true;
                }
            }
        }
        $this->dispatcher->dispatch(new Test\Phpunit_Warning_Triggered($this->telemetry_info(), $test, $message, $ignored_by_test));
    }
    /**
     * @param non-empty-string $output
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_printed_unexpected_output(string $output): void
    {
        $this->dispatcher->dispatch(new Test\Printed_Unexpected_Output($this->telemetry_info(), $output));
    }
    /**
     * @param non-empty-string $additionalInformation
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_provided_additional_information(Test_Method $test, string $additional_information): void
    {
        $this->dispatcher->dispatch(new Test\Additional_Information_Provided($this->telemetry_info(), $test, $additional_information));
    }
    /**
     * @param non-negative-int $numberOfAssertionsPerformed
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_finished(Code\Test $test, int $number_of_assertions_performed): void
    {
        $this->dispatcher->dispatch(new Test\Finished($this->telemetry_info(), $test, $number_of_assertions_performed));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function post_condition_called(Test_Method $test, Class_Method $called_method): void
    {
        $this->dispatcher->dispatch(new Test\Post_Condition_Called($this->telemetry_info(), $test, $called_method));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function post_condition_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Post_Condition_Errored($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function post_condition_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\Post_Condition_Failed($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function post_condition_finished(Test_Method $test, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Test\Post_Condition_Finished($this->telemetry_info(), $test, ...$called_methods));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_test_method_called(Test_Method $test, Class_Method $called_method): void
    {
        $this->dispatcher->dispatch(new Test\After_Test_Method_Called($this->telemetry_info(), $test, $called_method));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_test_method_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\After_Test_Method_Errored($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_test_method_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\After_Test_Method_Failed($this->telemetry_info(), $test, $called_method, $throwable));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_test_method_finished(Test_Method $test, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Test\After_Test_Method_Finished($this->telemetry_info(), $test, ...$called_methods));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_last_test_method_called(string $test_class_name, Class_Method $called_method): void
    {
        $this->dispatcher->dispatch(new Test\After_Last_Test_Method_Called($this->telemetry_info(), $test_class_name, $called_method));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_last_test_method_errored(string $test_class_name, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\After_Last_Test_Method_Errored($this->telemetry_info(), $test_class_name, $called_method, $throwable));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_last_test_method_failed(string $test_class_name, Class_Method $called_method, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(new Test\After_Last_Test_Method_Failed($this->telemetry_info(), $test_class_name, $called_method, $throwable));
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function after_last_test_method_finished(string $test_class_name, Class_Method ...$called_methods): void
    {
        $this->dispatcher->dispatch(new Test\After_Last_Test_Method_Finished($this->telemetry_info(), $test_class_name, ...$called_methods));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_suite_finished(Test_Suite $test_suite): void
    {
        $this->dispatcher->dispatch(new Test_Suite_Finished($this->telemetry_info(), $test_suite));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_started_static_analysis_for_code_coverage(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Static_Analysis_For_Code_Coverage_Started($this->telemetry_info()));
    }
    /**
     * @param non-negative-int $cacheHits
     * @param non-negative-int $cacheMisses
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_finished_static_analysis_for_code_coverage(int $cache_hits, int $cache_misses): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Static_Analysis_For_Code_Coverage_Finished($this->telemetry_info(), $cache_hits, $cache_misses));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_triggered_phpunit_deprecation(string $message): void
    {
        try {
            if (Test_Method_Builder::from_call_stack()->metadata()->is_ignore_phpunit_deprecations()->is_not_empty()) {
                return;
            }
        } catch (No_Test_Case_Object_On_Call_Stack_Exception) {
        }
        $this->dispatcher->dispatch(new Test_Runner\Deprecation_Triggered($this->telemetry_info(), $message));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_triggered_phpunit_notice(string $message): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Notice_Triggered($this->telemetry_info(), $message));
    }
    /**
     * @param non-empty-string $message
     *
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_triggered_phpunit_warning(string $message): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Warning_Triggered($this->telemetry_info(), $message));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_enabled_garbage_collection(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Garbage_Collection_Enabled($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_execution_aborted(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Execution_Aborted($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_execution_finished(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Execution_Finished($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function test_runner_finished(): void
    {
        $this->dispatcher->dispatch(new Test_Runner\Finished($this->telemetry_info()));
    }
    /**
     * @throws InvalidArgumentException
     * @throws UnknownEventTypeException
     */
    public function application_finished(int $shell_exit_code): void
    {
        $this->dispatcher->dispatch(new Application\Finished($this->telemetry_info(), $shell_exit_code));
    }
    /**
     * @throws InvalidArgumentException
     */
    private function telemetry_info(): Telemetry\Info
    {
        $current = $this->system->snapshot();
        $info = new Telemetry\Info($current, $current->time()->duration($this->start_snapshot->time()), $current->memory_usage()->diff($this->start_snapshot->memory_usage()), $current->time()->duration($this->previous_snapshot->time()), $current->memory_usage()->diff($this->previous_snapshot->memory_usage()));
        $this->previous_snapshot = $current;
        return $info;
    }
}