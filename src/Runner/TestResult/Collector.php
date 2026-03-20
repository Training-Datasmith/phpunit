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
namespace Php_Unit\Test_Runner\Test_Result;

use function array_values;
use function assert;
use function count;
use function implode;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Facade;
use Php_Unit\Event\Test\After_Last_Test_Method_Errored;
use Php_Unit\Event\Test\After_Last_Test_Method_Failed;
use Php_Unit\Event\Test\Before_First_Test_Method_Errored;
use Php_Unit\Event\Test\Before_First_Test_Method_Failed;
use Php_Unit\Event\Test\Considered_Risky;
use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Errored;
use Php_Unit\Event\Test\Error_Triggered;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Finished;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Php_Deprecation_Triggered;
use Php_Unit\Event\Test\Php_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Deprecation_Triggered;
use Php_Unit\Event\Test\Phpunit_Error_Triggered;
use Php_Unit\Event\Test\Phpunit_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Warning_Triggered;
use Php_Unit\Event\Test\Php_Warning_Triggered;
use Php_Unit\Event\Test\Skipped as TestSkipped;
use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Event\Test_Runner\Deprecation_Triggered as TestRunnerDeprecationTriggered;
use Php_Unit\Event\Test_Runner\Execution_Started;
use Php_Unit\Event\Test_Runner\Notice_Triggered as TestRunnerNoticeTriggered;
use Php_Unit\Event\Test_Runner\Warning_Triggered as TestRunnerWarningTriggered;
use Php_Unit\Event\Test_Suite\Finished as TestSuiteFinished;
use Php_Unit\Event\Test_Suite\Skipped as TestSuiteSkipped;
use Php_Unit\Event\Test_Suite\Started as TestSuiteStarted;
use Php_Unit\Event\Test_Suite\Test_Suite_For_Test_Class;
use Php_Unit\Event\Test_Suite\Test_Suite_For_Test_Method_With_Data_Provider;
use Php_Unit\Test_Runner\Issue_Filter;
use Php_Unit\Test_Runner\Test_Result\Issues\Issue;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collector
{
    private int $number_of_tests = 0;
    private int $number_of_tests_run = 0;
    private int $number_of_assertions = 0;
    private bool $prepared = false;
    private bool $child_process_errored = false;
    /**
     * @var non-negative-int
     */
    private int $number_of_issues_ignored_by_baseline = 0;
    /**
     * @var list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored>
     */
    private array $test_errored_events = [];
    /**
     * @var list<AfterLastTestMethodFailed|BeforeFirstTestMethodFailed|Failed>
     */
    private array $test_failed_events = [];
    /**
     * @var list<MarkedIncomplete>
     */
    private array $test_marked_incomplete_events = [];
    /**
     * @var list<TestSuiteSkipped>
     */
    private array $test_suite_skipped_events = [];
    /**
     * @var list<TestSkipped>
     */
    private array $test_skipped_events = [];
    /**
     * @var array<string,list<ConsideredRisky>>
     */
    private array $test_considered_risky_events = [];
    /**
     * @var array<string,list<PhpunitDeprecationTriggered>>
     */
    private array $test_triggered_phpunit_deprecation_events = [];
    /**
     * @var array<string,list<PhpunitErrorTriggered>>
     */
    private array $test_triggered_phpunit_error_events = [];
    /**
     * @var array<string,list<PhpunitNoticeTriggered>>
     */
    private array $test_triggered_phpunit_notice_events = [];
    /**
     * @var array<string,list<PhpunitWarningTriggered>>
     */
    private array $test_triggered_phpunit_warning_events = [];
    /**
     * @var list<TestRunnerDeprecationTriggered>
     */
    private array $test_runner_triggered_deprecation_events = [];
    /**
     * @var list<TestRunnerNoticeTriggered>
     */
    private array $test_runner_triggered_notice_events = [];
    /**
     * @var list<TestRunnerWarningTriggered>
     */
    private array $test_runner_triggered_warning_events = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $errors = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $deprecations = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $notices = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $warnings = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $php_deprecations = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $php_notices = [];
    /**
     * @var array<non-empty-string, Issue>
     */
    private array $php_warnings = [];
    public function __construct(Facade $facade, private readonly Issue_Filter $issue_filter)
    {
        $facade->register_subscribers(new Execution_Started_Subscriber($this), new Test_Suite_Skipped_Subscriber($this), new Test_Suite_Started_Subscriber($this), new Test_Suite_Finished_Subscriber($this), new Test_Prepared_Subscriber($this), new Test_Finished_Subscriber($this), new Before_Test_Class_Method_Errored_Subscriber($this), new Before_Test_Class_Method_Failed_Subscriber($this), new After_Test_Class_Method_Errored_Subscriber($this), new After_Test_Class_Method_Failed_Subscriber($this), new Test_Errored_Subscriber($this), new Test_Failed_Subscriber($this), new Test_Marked_Incomplete_Subscriber($this), new Test_Skipped_Subscriber($this), new Test_Considered_Risky_Subscriber($this), new Test_Triggered_Deprecation_Subscriber($this), new Test_Triggered_Error_Subscriber($this), new Test_Triggered_Notice_Subscriber($this), new Test_Triggered_Php_Deprecation_Subscriber($this), new Test_Triggered_Php_Notice_Subscriber($this), new Test_Triggered_Phpunit_Deprecation_Subscriber($this), new Test_Triggered_Phpunit_Error_Subscriber($this), new Test_Triggered_Phpunit_Notice_Subscriber($this), new Test_Triggered_Phpunit_Warning_Subscriber($this), new Test_Triggered_Php_Warning_Subscriber($this), new Test_Triggered_Warning_Subscriber($this), new Test_Runner_Triggered_Deprecation_Subscriber($this), new Test_Runner_Triggered_Notice_Subscriber($this), new Test_Runner_Triggered_Warning_Subscriber($this), new Child_Process_Errored_Subscriber($this));
    }
    public function result(): Test_Result
    {
        return new Test_Result($this->number_of_tests, $this->number_of_tests_run, $this->number_of_assertions, $this->test_errored_events, $this->test_failed_events, $this->test_considered_risky_events, $this->test_suite_skipped_events, $this->test_skipped_events, $this->test_marked_incomplete_events, $this->test_triggered_phpunit_deprecation_events, $this->test_triggered_phpunit_error_events, $this->test_triggered_phpunit_notice_events, $this->test_triggered_phpunit_warning_events, $this->test_runner_triggered_deprecation_events, $this->test_runner_triggered_notice_events, $this->test_runner_triggered_warning_events, array_values($this->errors), array_values($this->deprecations), array_values($this->notices), array_values($this->warnings), array_values($this->php_deprecations), array_values($this->php_notices), array_values($this->php_warnings), $this->number_of_issues_ignored_by_baseline);
    }
    public function execution_started(Execution_Started $event): void
    {
        $this->number_of_tests = $event->test_suite()->count();
    }
    public function test_suite_skipped(Test_Suite_Skipped $event): void
    {
        $test_suite = $event->test_suite();
        if (!$test_suite->is_for_test_class()) {
            return;
        }
        $this->test_suite_skipped_events[] = $event;
        $this->number_of_tests_run += $event->test_suite()->count();
    }
    public function test_suite_started(Test_Suite_Started $event): void
    {
        $test_suite = $event->test_suite();
        if (!$test_suite->is_for_test_class()) {
        }
    }
    public function test_suite_finished(Test_Suite_Finished $event): void
    {
        $test_suite = $event->test_suite();
        if ($test_suite->is_with_name()) {
            return;
        }
        if ($test_suite->is_for_test_method_with_data_provider()) {
            assert($test_suite instanceof Test_Suite_For_Test_Method_With_Data_Provider);
            assert(count($test_suite->tests()->as_array()) > 0);
            $test = $test_suite->tests()->as_array()[0];
            assert($test instanceof Test_Method);
            foreach ($this->test_failed_events as $test_failed_event) {
                if ($test_failed_event->test()->is_test_method() && $test_failed_event->test()->method_name() === $test->method_name()) {
                    return;
                }
            }
            Passed_Tests::instance()->test_method_passed($test, null);
            return;
        }
        assert($test_suite instanceof Test_Suite_For_Test_Class);
        Passed_Tests::instance()->test_class_passed($test_suite->class_name());
    }
    public function test_prepared(): void
    {
        $this->prepared = true;
    }
    public function test_finished(Finished $event): void
    {
        $this->number_of_assertions += $event->number_of_assertions_performed();
        $this->number_of_tests_run++;
        $this->prepared = false;
        $this->child_process_errored = false;
    }
    public function before_test_class_method_errored(Before_First_Test_Method_Errored $event): void
    {
        $this->test_errored_events[] = $event;
        $this->number_of_tests_run++;
    }
    public function before_test_class_method_failed(Before_First_Test_Method_Failed $event): void
    {
        $this->test_failed_events[] = $event;
        $this->number_of_tests_run++;
    }
    public function after_test_class_method_errored(After_Last_Test_Method_Errored $event): void
    {
        $this->test_errored_events[] = $event;
    }
    public function after_test_class_method_failed(After_Last_Test_Method_Failed $event): void
    {
        $this->test_failed_events[] = $event;
    }
    public function test_errored(Errored $event): void
    {
        $this->test_errored_events[] = $event;
        if ($this->child_process_errored) {
            return;
        }
        if (!$this->prepared) {
            $this->number_of_tests_run++;
        }
    }
    public function test_failed(Failed $event): void
    {
        $this->test_failed_events[] = $event;
    }
    public function test_marked_incomplete(Marked_Incomplete $event): void
    {
        $this->test_marked_incomplete_events[] = $event;
    }
    public function test_skipped(Test_Skipped $event): void
    {
        $this->test_skipped_events[] = $event;
        if (!$this->prepared) {
            $this->number_of_tests_run++;
        }
    }
    public function test_considered_risky(Considered_Risky $event): void
    {
        if (!isset($this->test_considered_risky_events[$event->test()->id()])) {
            $this->test_considered_risky_events[$event->test()->id()] = [];
        }
        $this->test_considered_risky_events[$event->test()->id()][] = $event;
    }
    public function test_triggered_deprecation(Deprecation_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            $this->number_of_issues_ignored_by_baseline++;
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->deprecations[$id])) {
            $this->deprecations[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test(), $event->stack_trace());
            return;
        }
        $this->deprecations[$id]->triggered_by($event->test());
    }
    public function test_triggered_php_deprecation(Php_Deprecation_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            $this->number_of_issues_ignored_by_baseline++;
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->php_deprecations[$id])) {
            $this->php_deprecations[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test());
            return;
        }
        $this->php_deprecations[$id]->triggered_by($event->test());
    }
    public function test_triggered_phpunit_deprecation(Phpunit_Deprecation_Triggered $event): void
    {
        if (!isset($this->test_triggered_phpunit_deprecation_events[$event->test()->id()])) {
            $this->test_triggered_phpunit_deprecation_events[$event->test()->id()] = [];
        }
        $this->test_triggered_phpunit_deprecation_events[$event->test()->id()][] = $event;
    }
    public function test_triggered_phpunit_notice(Phpunit_Notice_Triggered $event): void
    {
        if (!isset($this->test_triggered_phpunit_notice_events[$event->test()->id()])) {
            $this->test_triggered_phpunit_notice_events[$event->test()->id()] = [];
        }
        $this->test_triggered_phpunit_notice_events[$event->test()->id()][] = $event;
    }
    public function test_triggered_error(Error_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->errors[$id])) {
            $this->errors[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test());
            return;
        }
        $this->errors[$id]->triggered_by($event->test());
    }
    public function test_triggered_notice(Notice_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            $this->number_of_issues_ignored_by_baseline++;
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->notices[$id])) {
            $this->notices[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test());
            return;
        }
        $this->notices[$id]->triggered_by($event->test());
    }
    public function test_triggered_php_notice(Php_Notice_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            $this->number_of_issues_ignored_by_baseline++;
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->php_notices[$id])) {
            $this->php_notices[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test());
            return;
        }
        $this->php_notices[$id]->triggered_by($event->test());
    }
    public function test_triggered_warning(Warning_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            $this->number_of_issues_ignored_by_baseline++;
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->warnings[$id])) {
            $this->warnings[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test());
            return;
        }
        $this->warnings[$id]->triggered_by($event->test());
    }
    public function test_triggered_php_warning(Php_Warning_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            $this->number_of_issues_ignored_by_baseline++;
            return;
        }
        $id = $this->issue_id($event);
        if (!isset($this->php_warnings[$id])) {
            $this->php_warnings[$id] = Issue::from($event->file(), $event->line(), $event->message(), $event->test());
            return;
        }
        $this->php_warnings[$id]->triggered_by($event->test());
    }
    public function test_triggered_phpunit_error(Phpunit_Error_Triggered $event): void
    {
        if (!isset($this->test_triggered_phpunit_error_events[$event->test()->id()])) {
            $this->test_triggered_phpunit_error_events[$event->test()->id()] = [];
        }
        $this->test_triggered_phpunit_error_events[$event->test()->id()][] = $event;
    }
    public function test_triggered_phpunit_warning(Phpunit_Warning_Triggered $event): void
    {
        if ($event->ignored_by_test()) {
            return;
        }
        if (!isset($this->test_triggered_phpunit_warning_events[$event->test()->id()])) {
            $this->test_triggered_phpunit_warning_events[$event->test()->id()] = [];
        }
        $this->test_triggered_phpunit_warning_events[$event->test()->id()][] = $event;
    }
    public function test_runner_triggered_deprecation(Test_Runner_Deprecation_Triggered $event): void
    {
        $this->test_runner_triggered_deprecation_events[] = $event;
    }
    public function test_runner_triggered_notice(Test_Runner_Notice_Triggered $event): void
    {
        $this->test_runner_triggered_notice_events[] = $event;
    }
    public function test_runner_triggered_warning(Test_Runner_Warning_Triggered $event): void
    {
        $this->test_runner_triggered_warning_events[] = $event;
    }
    public function child_process_errored(): void
    {
        $this->child_process_errored = true;
    }
    public function has_errored_tests(): bool
    {
        return $this->test_errored_events !== [];
    }
    public function has_failed_tests(): bool
    {
        return $this->test_failed_events !== [];
    }
    public function has_risky_tests(): bool
    {
        return $this->test_considered_risky_events !== [];
    }
    public function has_skipped_tests(): bool
    {
        return $this->test_skipped_events !== [];
    }
    public function has_incomplete_tests(): bool
    {
        return $this->test_marked_incomplete_events !== [];
    }
    public function has_deprecations(): bool
    {
        return $this->deprecations !== [] || $this->php_deprecations !== [] || $this->test_triggered_phpunit_deprecation_events !== [] || $this->test_runner_triggered_deprecation_events !== [];
    }
    public function has_notices(): bool
    {
        return $this->notices !== [] || $this->php_notices !== [];
    }
    public function has_warnings(): bool
    {
        return $this->warnings !== [] || $this->php_warnings !== [] || $this->test_triggered_phpunit_warning_events !== [] || $this->test_runner_triggered_warning_events !== [];
    }
    /**
     * @return non-empty-string
     */
    private function issue_id(Deprecation_Triggered|Error_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Php_Warning_Triggered|Warning_Triggered $event): string
    {
        return implode(':', [$event->file(), $event->line(), $event->message()]);
    }
}