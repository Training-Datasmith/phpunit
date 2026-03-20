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

use function array_map;
use function array_sum;
use function count;
use Php_Unit\Event\Test\After_Last_Test_Method_Errored;
use Php_Unit\Event\Test\After_Last_Test_Method_Failed;
use Php_Unit\Event\Test\Before_First_Test_Method_Errored;
use Php_Unit\Event\Test\Before_First_Test_Method_Failed;
use Php_Unit\Event\Test\Considered_Risky;
use Php_Unit\Event\Test\Errored;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Phpunit_Deprecation_Triggered;
use Php_Unit\Event\Test\Phpunit_Error_Triggered;
use Php_Unit\Event\Test\Phpunit_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Warning_Triggered;
use Php_Unit\Event\Test\Skipped as TestSkipped;
use Php_Unit\Event\Test_Runner\Deprecation_Triggered as TestRunnerDeprecationTriggered;
use Php_Unit\Event\Test_Runner\Notice_Triggered as TestRunnerNoticeTriggered;
use Php_Unit\Event\Test_Runner\Warning_Triggered as TestRunnerWarningTriggered;
use Php_Unit\Event\Test_Suite\Skipped as TestSuiteSkipped;
use Php_Unit\Test_Runner\Test_Result\Issues\Issue;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Result
{
    /**
     * @param list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored> $testErroredEvents
     * @param list<AfterLastTestMethodFailed|BeforeFirstTestMethodFailed|Failed>    $testFailedEvents
     * @param array<string,list<ConsideredRisky>>                                   $testConsideredRiskyEvents
     * @param list<TestSuiteSkipped>                                                $testSuiteSkippedEvents
     * @param list<TestSkipped>                                                     $testSkippedEvents
     * @param list<MarkedIncomplete>                                                $testMarkedIncompleteEvents
     * @param array<string,list<PhpunitDeprecationTriggered>>                       $testTriggeredPhpunitDeprecationEvents
     * @param array<string,list<PhpunitErrorTriggered>>                             $testTriggeredPhpunitErrorEvents
     * @param array<string,list<PhpunitNoticeTriggered>>                            $testTriggeredPhpunitNoticeEvents
     * @param array<string,list<PhpunitWarningTriggered>>                           $testTriggeredPhpunitWarningEvents
     * @param list<TestRunnerDeprecationTriggered>                                  $testRunnerTriggeredDeprecationEvents
     * @param list<TestRunnerNoticeTriggered>                                       $testRunnerTriggeredNoticeEvents
     * @param list<TestRunnerWarningTriggered>                                      $testRunnerTriggeredWarningEvents
     * @param list<Issue>                                                           $errors
     * @param list<Issue>                                                           $deprecations
     * @param list<Issue>                                                           $notices
     * @param list<Issue>                                                           $warnings
     * @param list<Issue>                                                           $phpDeprecations
     * @param list<Issue>                                                           $phpNotices
     * @param list<Issue>                                                           $phpWarnings
     * @param non-negative-int                                                      $numberOfIssuesIgnoredByBaseline
     */
    public function __construct(private int $number_of_tests, private int $number_of_tests_run, private int $number_of_assertions, private array $test_errored_events, private array $test_failed_events, private array $test_considered_risky_events, private array $test_suite_skipped_events, private array $test_skipped_events, private array $test_marked_incomplete_events, private array $test_triggered_phpunit_deprecation_events, private array $test_triggered_phpunit_error_events, private array $test_triggered_phpunit_notice_events, private array $test_triggered_phpunit_warning_events, private array $test_runner_triggered_deprecation_events, private array $test_runner_triggered_notice_events, private array $test_runner_triggered_warning_events, private array $errors, private array $deprecations, private array $notices, private array $warnings, private array $php_deprecations, private array $php_notices, private array $php_warnings, private int $number_of_issues_ignored_by_baseline)
    {
    }
    public function number_of_tests_run(): int
    {
        return $this->number_of_tests_run;
    }
    public function number_of_assertions(): int
    {
        return $this->number_of_assertions;
    }
    /**
     * @return list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored>
     */
    public function test_errored_events(): array
    {
        return $this->test_errored_events;
    }
    public function number_of_test_errored_events(): int
    {
        return count($this->test_errored_events);
    }
    public function has_test_errored_events(): bool
    {
        return $this->number_of_test_errored_events() > 0;
    }
    /**
     * @return list<Failed>
     */
    public function test_failed_events(): array
    {
        return $this->test_failed_events;
    }
    public function number_of_test_failed_events(): int
    {
        return count($this->test_failed_events);
    }
    public function has_test_failed_events(): bool
    {
        return $this->number_of_test_failed_events() > 0;
    }
    /**
     * @return array<string,list<ConsideredRisky>>
     */
    public function test_considered_risky_events(): array
    {
        return $this->test_considered_risky_events;
    }
    public function number_of_tests_with_test_considered_risky_events(): int
    {
        return count($this->test_considered_risky_events);
    }
    public function has_test_considered_risky_events(): bool
    {
        return $this->number_of_tests_with_test_considered_risky_events() > 0;
    }
    /**
     * @return list<TestSuiteSkipped>
     */
    public function test_suite_skipped_events(): array
    {
        return $this->test_suite_skipped_events;
    }
    public function number_of_test_skipped_by_test_suite_skipped_events(): int
    {
        return array_sum(array_map(static fn(Test_Suite_Skipped $event): int => $event->test_suite()->count(), $this->test_suite_skipped_events));
    }
    public function has_test_suite_skipped_events(): bool
    {
        return $this->number_of_test_skipped_by_test_suite_skipped_events() > 0;
    }
    /**
     * @return list<TestSkipped>
     */
    public function test_skipped_events(): array
    {
        return $this->test_skipped_events;
    }
    public function number_of_test_skipped_events(): int
    {
        return count($this->test_skipped_events);
    }
    public function has_test_skipped_events(): bool
    {
        return $this->number_of_test_skipped_events() > 0;
    }
    /**
     * @return list<MarkedIncomplete>
     */
    public function test_marked_incomplete_events(): array
    {
        return $this->test_marked_incomplete_events;
    }
    public function number_of_test_marked_incomplete_events(): int
    {
        return count($this->test_marked_incomplete_events);
    }
    public function has_test_marked_incomplete_events(): bool
    {
        return $this->number_of_test_marked_incomplete_events() > 0;
    }
    /**
     * @return array<string,list<PhpunitDeprecationTriggered>>
     */
    public function test_triggered_phpunit_deprecation_events(): array
    {
        return $this->test_triggered_phpunit_deprecation_events;
    }
    public function number_of_tests_with_test_triggered_phpunit_deprecation_events(): int
    {
        return count($this->test_triggered_phpunit_deprecation_events);
    }
    public function has_test_triggered_phpunit_deprecation_events(): bool
    {
        return $this->number_of_tests_with_test_triggered_phpunit_deprecation_events() > 0;
    }
    /**
     * @return array<string,list<PhpunitErrorTriggered>>
     */
    public function test_triggered_phpunit_error_events(): array
    {
        return $this->test_triggered_phpunit_error_events;
    }
    public function number_of_tests_with_test_triggered_phpunit_error_events(): int
    {
        return count($this->test_triggered_phpunit_error_events);
    }
    public function has_test_triggered_phpunit_error_events(): bool
    {
        return $this->number_of_tests_with_test_triggered_phpunit_error_events() > 0;
    }
    /**
     * @return array<string,list<PhpunitNoticeTriggered>>
     */
    public function test_triggered_phpunit_notice_events(): array
    {
        return $this->test_triggered_phpunit_notice_events;
    }
    public function number_of_tests_with_test_triggered_phpunit_notice_events(): int
    {
        return count($this->test_triggered_phpunit_notice_events);
    }
    public function has_test_triggered_phpunit_notice_events(): bool
    {
        return $this->number_of_tests_with_test_triggered_phpunit_notice_events() > 0;
    }
    /**
     * @return array<string,list<PhpunitWarningTriggered>>
     */
    public function test_triggered_phpunit_warning_events(): array
    {
        return $this->test_triggered_phpunit_warning_events;
    }
    public function number_of_tests_with_test_triggered_phpunit_warning_events(): int
    {
        return count($this->test_triggered_phpunit_warning_events);
    }
    public function has_test_triggered_phpunit_warning_events(): bool
    {
        return $this->number_of_tests_with_test_triggered_phpunit_warning_events() > 0;
    }
    /**
     * @return list<TestRunnerDeprecationTriggered>
     */
    public function test_runner_triggered_deprecation_events(): array
    {
        return $this->test_runner_triggered_deprecation_events;
    }
    public function number_of_test_runner_triggered_deprecation_events(): int
    {
        return count($this->test_runner_triggered_deprecation_events);
    }
    public function has_test_runner_triggered_deprecation_events(): bool
    {
        return $this->number_of_test_runner_triggered_deprecation_events() > 0;
    }
    /**
     * @return list<TestRunnerNoticeTriggered>
     */
    public function test_runner_triggered_notice_events(): array
    {
        return $this->test_runner_triggered_notice_events;
    }
    public function number_of_test_runner_triggered_notice_events(): int
    {
        return count($this->test_runner_triggered_notice_events);
    }
    public function has_test_runner_triggered_notice_events(): bool
    {
        return $this->number_of_test_runner_triggered_notice_events() > 0;
    }
    /**
     * @return list<TestRunnerWarningTriggered>
     */
    public function test_runner_triggered_warning_events(): array
    {
        return $this->test_runner_triggered_warning_events;
    }
    public function number_of_test_runner_triggered_warning_events(): int
    {
        return count($this->test_runner_triggered_warning_events);
    }
    public function has_test_runner_triggered_warning_events(): bool
    {
        return $this->number_of_test_runner_triggered_warning_events() > 0;
    }
    public function was_successful(): bool
    {
        return !$this->has_test_errored_events() && !$this->has_test_failed_events() && !$this->has_test_triggered_phpunit_error_events();
    }
    public function has_issues(): bool
    {
        if ($this->has_tests_with_issues()) {
            return true;
        }
        return $this->has_test_runner_triggered_warning_events();
    }
    public function has_tests_with_issues(): bool
    {
        if ($this->has_risky_tests()) {
            return true;
        }
        if ($this->has_incomplete_tests()) {
            return true;
        }
        if ($this->has_deprecations()) {
            return true;
        }
        if ($this->errors !== []) {
            return true;
        }
        if ($this->has_notices()) {
            return true;
        }
        if ($this->has_warnings()) {
            return true;
        }
        if ($this->has_phpunit_notices()) {
            return true;
        }
        return $this->has_phpunit_warnings();
    }
    /**
     * @return list<Issue>
     */
    public function errors(): array
    {
        return $this->errors;
    }
    /**
     * @return list<Issue>
     */
    public function deprecations(): array
    {
        return $this->deprecations;
    }
    /**
     * @return list<Issue>
     */
    public function notices(): array
    {
        return $this->notices;
    }
    /**
     * @return list<Issue>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
    /**
     * @return list<Issue>
     */
    public function php_deprecations(): array
    {
        return $this->php_deprecations;
    }
    /**
     * @return list<Issue>
     */
    public function php_notices(): array
    {
        return $this->php_notices;
    }
    /**
     * @return list<Issue>
     */
    public function php_warnings(): array
    {
        return $this->php_warnings;
    }
    public function has_tests(): bool
    {
        return $this->number_of_tests > 0;
    }
    public function has_errors(): bool
    {
        return $this->number_of_errors() > 0;
    }
    public function number_of_errors(): int
    {
        return $this->number_of_test_errored_events() + count($this->errors) + $this->number_of_tests_with_test_triggered_phpunit_error_events();
    }
    public function has_deprecations(): bool
    {
        return $this->number_of_deprecations() > 0;
    }
    public function has_php_or_user_deprecations(): bool
    {
        return $this->number_of_php_or_user_deprecations() > 0;
    }
    public function number_of_php_or_user_deprecations(): int
    {
        return count($this->deprecations) + count($this->php_deprecations);
    }
    public function has_phpunit_deprecations(): bool
    {
        return $this->number_of_phpunit_deprecations() > 0;
    }
    public function number_of_phpunit_deprecations(): int
    {
        return count($this->test_triggered_phpunit_deprecation_events) + count($this->test_runner_triggered_deprecation_events);
    }
    public function has_phpunit_warnings(): bool
    {
        return $this->number_of_phpunit_warnings() > 0;
    }
    public function number_of_phpunit_warnings(): int
    {
        return count($this->test_triggered_phpunit_warning_events) + count($this->test_runner_triggered_warning_events);
    }
    public function number_of_deprecations(): int
    {
        return count($this->deprecations) + count($this->php_deprecations) + count($this->test_triggered_phpunit_deprecation_events) + count($this->test_runner_triggered_deprecation_events);
    }
    public function has_notices(): bool
    {
        return $this->number_of_notices() > 0;
    }
    public function number_of_notices(): int
    {
        return count($this->notices) + count($this->php_notices);
    }
    public function has_warnings(): bool
    {
        return $this->number_of_warnings() > 0;
    }
    public function number_of_warnings(): int
    {
        return count($this->warnings) + count($this->php_warnings);
    }
    public function has_incomplete_tests(): bool
    {
        return $this->test_marked_incomplete_events !== [];
    }
    public function has_risky_tests(): bool
    {
        return $this->test_considered_risky_events !== [];
    }
    public function has_skipped_tests(): bool
    {
        return $this->test_skipped_events !== [];
    }
    public function has_issues_ignored_by_baseline(): bool
    {
        return $this->number_of_issues_ignored_by_baseline > 0;
    }
    /**
     * @return non-negative-int
     */
    public function number_of_issues_ignored_by_baseline(): int
    {
        return $this->number_of_issues_ignored_by_baseline;
    }
    public function has_phpunit_notices(): bool
    {
        return $this->number_of_phpunit_notices() > 0;
    }
    public function number_of_phpunit_notices(): int
    {
        return $this->number_of_tests_with_test_triggered_phpunit_notice_events() + $this->number_of_test_runner_triggered_notice_events();
    }
}