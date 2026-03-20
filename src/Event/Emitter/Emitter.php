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

use Php_Unit\Event\Code\Class_Method;
use Php_Unit\Event\Code\Comparison_Failure;
use Php_Unit\Event\Code\Issue_Trigger\Issue_Trigger;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Code\Throwable;
use Php_Unit\Event\Test_Suite\Test_Suite;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Sebastian_Bergmann\Comparator\Comparator;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Emitter
{
    public function application_started(): void;
    public function test_runner_started(): void;
    public function test_runner_configured(Configuration $configuration): void;
    /**
     * @param non-empty-string $filename
     */
    public function test_runner_bootstrap_finished(string $filename): void;
    /**
     * @param non-empty-string $filename
     * @param non-empty-string $name
     * @param non-empty-string $version
     */
    public function test_runner_loaded_extension_from_phar(string $filename, string $name, string $version): void;
    /**
     * @param class-string          $className
     * @param array<string, string> $parameters
     */
    public function test_runner_bootstrapped_extension(string $class_name, array $parameters): void;
    public function data_provider_method_called(Class_Method $test_method, Class_Method $data_provider_method): void;
    public function data_provider_method_finished(Class_Method $test_method, Class_Method ...$called_methods): void;
    public function test_suite_loaded(Test_Suite $test_suite): void;
    public function test_suite_filtered(Test_Suite $test_suite): void;
    public function test_suite_sorted(int $execution_order, int $execution_order_defects, bool $resolve_dependencies): void;
    public function test_runner_event_facade_sealed(): void;
    public function test_runner_execution_started(Test_Suite $test_suite): void;
    public function test_runner_disabled_garbage_collection(): void;
    public function test_runner_triggered_garbage_collection(): void;
    /**
     * @param non-empty-string $message
     */
    public function test_suite_skipped(Test_Suite $test_suite, string $message): void;
    public function test_suite_started(Test_Suite $test_suite): void;
    public function test_preparation_started(Code\Test $test): void;
    public function test_preparation_errored(Code\Test $test, Throwable $throwable): void;
    public function test_preparation_failed(Code\Test $test, Throwable $throwable): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function before_first_test_method_called(string $test_class_name, Class_Method $called_method): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function before_first_test_method_errored(string $test_class_name, Class_Method $called_method, Throwable $throwable): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function before_first_test_method_failed(string $test_class_name, Class_Method $called_method, Throwable $throwable): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function before_first_test_method_finished(string $test_class_name, Class_Method ...$called_methods): void;
    public function before_test_method_called(Test_Method $test, Class_Method $called_method): void;
    public function before_test_method_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function before_test_method_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function before_test_method_finished(Test_Method $test, Class_Method ...$called_methods): void;
    public function pre_condition_called(Test_Method $test, Class_Method $called_method): void;
    public function pre_condition_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function pre_condition_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function pre_condition_finished(Test_Method $test, Class_Method ...$called_methods): void;
    public function test_prepared(Code\Test $test): void;
    /**
     * @param class-string<Comparator> $className
     */
    public function test_registered_comparator(string $class_name): void;
    public function test_used_custom_method_invocation(Test_Method $test, Class_Method $custom_test_method_invocation): void;
    /**
     * @param class-string $className
     */
    public function test_created_mock_object(string $class_name): void;
    /**
     * @param list<class-string> $interfaces
     */
    public function test_created_mock_object_for_intersection_of_interfaces(array $interfaces): void;
    /**
     * @param class-string $className
     */
    public function test_created_partial_mock_object(string $class_name, string ...$method_names): void;
    /**
     * @param class-string $className
     */
    public function test_created_stub(string $class_name): void;
    /**
     * @param list<class-string> $interfaces
     */
    public function test_created_stub_for_intersection_of_interfaces(array $interfaces): void;
    public function test_errored(Code\Test $test, Throwable $throwable): void;
    public function test_failed(Code\Test $test, Throwable $throwable, ?Comparison_Failure $comparison_failure): void;
    public function test_passed(Code\Test $test): void;
    /**
     * @param non-empty-string $message
     */
    public function test_considered_risky(Code\Test $test, string $message): void;
    public function test_marked_as_incomplete(Code\Test $test, Throwable $throwable): void;
    /**
     * @param non-empty-string $message
     */
    public function test_skipped(Code\Test $test, string $message): void;
    /**
     * @param non-empty-string $message
     */
    public function test_triggered_phpunit_deprecation(?Code\Test $test, string $message): void;
    /**
     * @param non-empty-string $message
     */
    public function test_triggered_phpunit_notice(Code\Test $test, string $message): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function test_triggered_php_deprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline, bool $ignored_by_test, Issue_Trigger $trigger): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $stackTrace
     */
    public function test_triggered_deprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline, bool $ignored_by_test, Issue_Trigger $trigger, string $stack_trace): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function test_triggered_error(Code\Test $test, string $message, string $file, int $line, bool $suppressed): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function test_triggered_notice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function test_triggered_php_notice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function test_triggered_warning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void;
    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function test_triggered_php_warning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignored_by_baseline): void;
    /**
     * @param non-empty-string $message
     */
    public function test_triggered_phpunit_error(Code\Test $test, string $message): void;
    /**
     * @param non-empty-string $message
     */
    public function test_triggered_phpunit_warning(Code\Test $test, string $message): void;
    /**
     * @param non-empty-string $output
     */
    public function test_printed_unexpected_output(string $output): void;
    /**
     * @param non-empty-string $additionalInformation
     */
    public function test_provided_additional_information(Test_Method $test, string $additional_information): void;
    /**
     * @param non-negative-int $numberOfAssertionsPerformed
     */
    public function test_finished(Code\Test $test, int $number_of_assertions_performed): void;
    public function post_condition_called(Test_Method $test, Class_Method $called_method): void;
    public function post_condition_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function post_condition_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function post_condition_finished(Test_Method $test, Class_Method ...$called_methods): void;
    public function after_test_method_called(Test_Method $test, Class_Method $called_method): void;
    public function after_test_method_errored(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function after_test_method_failed(Test_Method $test, Class_Method $called_method, Throwable $throwable): void;
    public function after_test_method_finished(Test_Method $test, Class_Method ...$called_methods): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function after_last_test_method_called(string $test_class_name, Class_Method $called_method): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function after_last_test_method_errored(string $test_class_name, Class_Method $called_method, Throwable $throwable): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function after_last_test_method_failed(string $test_class_name, Class_Method $called_method, Throwable $throwable): void;
    /**
     * @param class-string<TestCase> $testClassName
     */
    public function after_last_test_method_finished(string $test_class_name, Class_Method ...$called_methods): void;
    public function test_suite_finished(Test_Suite $test_suite): void;
    public function child_process_started(): void;
    public function child_process_errored(): void;
    public function child_process_finished(string $stdout, string $stderr): void;
    public function test_runner_started_static_analysis_for_code_coverage(): void;
    /**
     * @param non-negative-int $cacheHits
     * @param non-negative-int $cacheMisses
     */
    public function test_runner_finished_static_analysis_for_code_coverage(int $cache_hits, int $cache_misses): void;
    /**
     * @param non-empty-string $message
     */
    public function test_runner_triggered_phpunit_deprecation(string $message): void;
    /**
     * @param non-empty-string $message
     */
    public function test_runner_triggered_phpunit_notice(string $message): void;
    /**
     * @param non-empty-string $message
     */
    public function test_runner_triggered_phpunit_warning(string $message): void;
    public function test_runner_enabled_garbage_collection(): void;
    public function test_runner_execution_aborted(): void;
    public function test_runner_execution_finished(): void;
    public function test_runner_finished(): void;
    public function application_finished(int $shell_exit_code): void;
}