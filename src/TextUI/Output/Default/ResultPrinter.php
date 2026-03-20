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
namespace Php_Unit\Text_Ui\Output\Default;

use function array_keys;
use function array_merge;
use function array_reverse;
use function array_unique;
use function assert;
use function count;
use function explode;
use function ksort;
use const PHP_EOL;
use Php_Unit\Event\Code\Test;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Test\After_Last_Test_Method_Errored;
use Php_Unit\Event\Test\Before_First_Test_Method_Errored;
use Php_Unit\Event\Test\Considered_Risky;
use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Error_Triggered;
use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Php_Deprecation_Triggered;
use Php_Unit\Event\Test\Php_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Deprecation_Triggered;
use Php_Unit\Event\Test\Phpunit_Error_Triggered;
use Php_Unit\Event\Test\Phpunit_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Warning_Triggered;
use Php_Unit\Event\Test\Php_Warning_Triggered;
use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Test_Runner\Test_Result\Issues\Issue;
use Php_Unit\Test_Runner\Test_Result\Test_Result;
use Php_Unit\Text_Ui\Output\Printer;
use function range;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Result_Printer
{
    private bool $list_printed = false;
    public function __construct(private readonly Printer $printer, private readonly bool $display_phpunit_deprecations, private readonly bool $display_phpunit_errors, private readonly bool $display_phpunit_notices, private readonly bool $display_phpunit_warnings, private readonly bool $display_tests_with_errors, private readonly bool $display_tests_with_failed_assertions, private readonly bool $display_risky_tests, private readonly bool $display_details_on_incomplete_tests, private readonly bool $display_details_on_skipped_tests, private readonly bool $display_details_on_tests_that_trigger_deprecations, private readonly bool $display_details_on_tests_that_trigger_errors, private readonly bool $display_details_on_tests_that_trigger_notices, private readonly bool $display_details_on_tests_that_trigger_warnings, private readonly bool $display_defects_in_reverse_order)
    {
    }
    public function print(Test_Result $result, bool $stack_trace_for_deprecations = false): void
    {
        if ($this->display_phpunit_errors) {
            $this->print_phpunit_errors($result);
        }
        if ($this->display_phpunit_warnings) {
            $this->print_test_runner_warnings($result);
        }
        if ($this->display_phpunit_deprecations) {
            $this->print_test_runner_deprecations($result);
        }
        if ($this->display_phpunit_notices) {
            $this->print_test_runner_notices($result);
        }
        if ($this->display_tests_with_errors) {
            $this->print_tests_with_errors($result);
        }
        if ($this->display_tests_with_failed_assertions) {
            $this->print_tests_with_failed_assertions($result);
        }
        if ($this->display_phpunit_warnings) {
            $this->print_details_on_tests_that_triggered_phpunit_warnings($result);
        }
        if ($this->display_phpunit_deprecations) {
            $this->print_details_on_tests_that_triggered_phpunit_deprecations($result);
        }
        if ($this->display_risky_tests) {
            $this->print_risky_tests($result);
        }
        if ($this->display_phpunit_notices) {
            $this->print_details_on_tests_that_triggered_phpunit_notices($result);
        }
        if ($this->display_details_on_incomplete_tests) {
            $this->print_incomplete_tests($result);
        }
        if ($this->display_details_on_skipped_tests) {
            $this->print_skipped_test_suites($result);
            $this->print_skipped_tests($result);
        }
        if ($this->display_details_on_tests_that_trigger_errors) {
            $this->print_issue_list('error', $result->errors());
        }
        if ($this->display_details_on_tests_that_trigger_warnings) {
            $this->print_issue_list('PHP warning', $result->php_warnings());
            $this->print_issue_list('warning', $result->warnings());
        }
        if ($this->display_details_on_tests_that_trigger_notices) {
            $this->print_issue_list('PHP notice', $result->php_notices());
            $this->print_issue_list('notice', $result->notices());
        }
        if ($this->display_details_on_tests_that_trigger_deprecations) {
            $this->print_issue_list('PHP deprecation', $result->php_deprecations());
            $this->print_issue_list('deprecation', $result->deprecations(), $stack_trace_for_deprecations);
        }
    }
    private function print_phpunit_errors(Test_Result $result): void
    {
        if (!$result->has_test_triggered_phpunit_error_events()) {
            return;
        }
        $elements = $this->map_tests_with_issues_events_to_elements($result->test_triggered_phpunit_error_events());
        $this->print_list_header_with_number($elements['numberOfTestsWithIssues'], 'PHPUnit error');
        $this->print_list($elements['elements']);
    }
    private function print_details_on_tests_that_triggered_phpunit_deprecations(Test_Result $result): void
    {
        if (!$result->has_test_triggered_phpunit_deprecation_events()) {
            return;
        }
        $elements = $this->map_tests_with_issues_events_to_elements($result->test_triggered_phpunit_deprecation_events());
        $this->print_list_header_with_number_of_tests_and_number_of_issues($elements['numberOfTestsWithIssues'], $elements['numberOfIssues'], 'PHPUnit deprecation');
        $this->print_list($elements['elements']);
    }
    private function print_details_on_tests_that_triggered_phpunit_notices(Test_Result $result): void
    {
        if (!$result->has_test_triggered_phpunit_notice_events()) {
            return;
        }
        $elements = $this->map_tests_with_issues_events_to_elements($result->test_triggered_phpunit_notice_events());
        $this->print_list_header_with_number_of_tests_and_number_of_issues($elements['numberOfTestsWithIssues'], $elements['numberOfIssues'], 'PHPUnit notice');
        $this->print_list($elements['elements']);
    }
    private function print_test_runner_notices(Test_Result $result): void
    {
        if (!$result->has_test_runner_triggered_notice_events()) {
            return;
        }
        $elements = [];
        $messages = [];
        foreach ($result->test_runner_triggered_notice_events() as $event) {
            if (isset($messages[$event->message()])) {
                continue;
            }
            $elements[] = ['title' => $event->message(), 'body' => ''];
            $messages[$event->message()] = true;
        }
        $this->print_list_header_with_number(count($elements), 'PHPUnit test runner notice');
        $this->print_list($elements);
    }
    private function print_test_runner_warnings(Test_Result $result): void
    {
        if (!$result->has_test_runner_triggered_warning_events()) {
            return;
        }
        $elements = [];
        $messages = [];
        foreach ($result->test_runner_triggered_warning_events() as $event) {
            if (isset($messages[$event->message()])) {
                continue;
            }
            $elements[] = ['title' => $event->message(), 'body' => ''];
            $messages[$event->message()] = true;
        }
        $this->print_list_header_with_number(count($elements), 'PHPUnit test runner warning');
        $this->print_list($elements);
    }
    private function print_test_runner_deprecations(Test_Result $result): void
    {
        if (!$result->has_test_runner_triggered_deprecation_events()) {
            return;
        }
        $elements = [];
        foreach ($result->test_runner_triggered_deprecation_events() as $event) {
            $elements[] = ['title' => $event->message(), 'body' => ''];
        }
        $this->print_list_header_with_number(count($elements), 'PHPUnit test runner deprecation');
        $this->print_list($elements);
    }
    private function print_details_on_tests_that_triggered_phpunit_warnings(Test_Result $result): void
    {
        if (!$result->has_test_triggered_phpunit_warning_events()) {
            return;
        }
        $elements = $this->map_tests_with_issues_events_to_elements($result->test_triggered_phpunit_warning_events());
        $this->print_list_header_with_number_of_tests_and_number_of_issues($elements['numberOfTestsWithIssues'], $elements['numberOfIssues'], 'PHPUnit warning');
        $this->print_list($elements['elements']);
    }
    private function print_tests_with_errors(Test_Result $result): void
    {
        if (!$result->has_test_errored_events()) {
            return;
        }
        $elements = [];
        foreach ($result->test_errored_events() as $event) {
            if ($event instanceof After_Last_Test_Method_Errored || $event instanceof Before_First_Test_Method_Errored) {
                $title = $event->test_class_name();
            } else {
                $title = $this->name($event->test());
            }
            $elements[] = ['title' => $title, 'body' => $event->throwable()->as_string()];
        }
        $this->print_list_header_with_number(count($elements), 'error');
        $this->print_list($elements);
    }
    private function print_tests_with_failed_assertions(Test_Result $result): void
    {
        if (!$result->has_test_failed_events()) {
            return;
        }
        $elements = [];
        foreach ($result->test_failed_events() as $event) {
            $body = $event->throwable()->as_string();
            if (str_starts_with($body, 'AssertionError: ')) {
                $body = substr($body, strlen('AssertionError: '));
            }
            $elements[] = ['title' => $this->name($event->test()), 'body' => $body];
        }
        $this->print_list_header_with_number(count($elements), 'failure');
        $this->print_list($elements);
    }
    private function print_risky_tests(Test_Result $result): void
    {
        if (!$result->has_test_considered_risky_events()) {
            return;
        }
        $elements = $this->map_tests_with_issues_events_to_elements($result->test_considered_risky_events());
        $this->print_list_header_with_number($elements['numberOfTestsWithIssues'], 'risky test');
        $this->print_list($elements['elements']);
    }
    private function print_incomplete_tests(Test_Result $result): void
    {
        if (!$result->has_test_marked_incomplete_events()) {
            return;
        }
        $elements = [];
        foreach ($result->test_marked_incomplete_events() as $event) {
            $elements[] = ['title' => $this->name($event->test()), 'body' => $event->throwable()->as_string()];
        }
        $this->print_list_header_with_number(count($elements), 'incomplete test');
        $this->print_list($elements);
    }
    private function print_skipped_test_suites(Test_Result $result): void
    {
        if (!$result->has_test_suite_skipped_events()) {
            return;
        }
        $elements = [];
        foreach ($result->test_suite_skipped_events() as $event) {
            $elements[] = ['title' => $event->test_suite()->name(), 'body' => $event->message()];
        }
        $this->print_list_header_with_number(count($elements), 'skipped test suite');
        $this->print_list($elements);
    }
    private function print_skipped_tests(Test_Result $result): void
    {
        if (!$result->has_test_skipped_events()) {
            return;
        }
        $elements = [];
        foreach ($result->test_skipped_events() as $event) {
            $elements[] = ['title' => $this->name($event->test()), 'body' => $event->message()];
        }
        $this->print_list_header_with_number(count($elements), 'skipped test');
        $this->print_list($elements);
    }
    /**
     * @param non-empty-string $type
     * @param list<Issue>      $issues
     */
    private function print_issue_list(string $type, array $issues, bool $stack_trace = false): void
    {
        if ($issues === []) {
            return;
        }
        $number_of_unique_issues = count($issues);
        $triggering_tests = [];
        foreach ($issues as $issue) {
            $triggering_tests = array_merge($triggering_tests, array_keys($issue->triggering_tests()));
        }
        $number_of_tests = count(array_unique($triggering_tests));
        unset($triggering_tests);
        $this->print_list_header(sprintf('%d test%s triggered %d %s%s:' . PHP_EOL . PHP_EOL, $number_of_tests, $number_of_tests !== 1 ? 's' : '', $number_of_unique_issues, $type, $number_of_unique_issues !== 1 ? 's' : ''));
        $i = 1;
        foreach ($issues as $issue) {
            $title = sprintf('%s:%d', $issue->file(), $issue->line());
            $body = trim($issue->description()) . PHP_EOL . PHP_EOL;
            if ($stack_trace && $issue->has_stack_trace()) {
                $body .= trim((string) $issue->stack_trace()) . PHP_EOL . PHP_EOL;
            }
            if (!$issue->triggered_in_test()) {
                $body .= 'Triggered by:';
                $triggering_tests = $issue->triggering_tests();
                ksort($triggering_tests);
                foreach ($triggering_tests as $triggering_test) {
                    $body .= PHP_EOL . PHP_EOL . '* ' . $triggering_test['test']->id();
                    if ($triggering_test['count'] > 1) {
                        $body .= sprintf(' (%d times)', $triggering_test['count']);
                    }
                    if ($triggering_test['test']->is_test_method()) {
                        $body .= PHP_EOL . '  ' . $triggering_test['test']->file() . ':' . $triggering_test['test']->line();
                    }
                }
            }
            $this->print_issue_list_element($i++, $title, $body);
            $this->printer->print(PHP_EOL);
        }
    }
    private function print_list_header_with_number_of_tests_and_number_of_issues(int $number_of_tests_with_issues, int $number_of_issues, string $type): void
    {
        $this->print_list_header(sprintf("%d test%s triggered %d %s%s:\n\n", $number_of_tests_with_issues, $number_of_tests_with_issues !== 1 ? 's' : '', $number_of_issues, $type, $number_of_issues !== 1 ? 's' : ''));
    }
    private function print_list_header_with_number(int $number, string $type): void
    {
        $this->print_list_header(sprintf("There %s %d %s%s:\n\n", $number === 1 ? 'was' : 'were', $number, $type, $number === 1 ? '' : 's'));
    }
    private function print_list_header(string $header): void
    {
        if ($this->list_printed) {
            $this->printer->print("--\n\n");
        }
        $this->list_printed = true;
        $this->printer->print($header);
    }
    /**
     * @param list<array{title: string, body: string}> $elements
     */
    private function print_list(array $elements): void
    {
        $i = 1;
        if ($this->display_defects_in_reverse_order) {
            $elements = array_reverse($elements);
        }
        foreach ($elements as $element) {
            $this->print_list_element($i++, $element['title'], $element['body']);
        }
        $this->printer->print("\n");
    }
    private function print_list_element(int $number, string $title, string $body): void
    {
        $body = trim($body);
        $this->printer->print(sprintf("%s%d) %s\n%s%s", $number > 1 ? "\n" : '', $number, $title, $body, $body !== '' ? "\n" : ''));
    }
    private function print_issue_list_element(int $number, string $title, string $body): void
    {
        $body = trim($body);
        $this->printer->print(sprintf("%d) %s\n%s%s", $number, $title, $body, $body !== '' ? "\n" : ''));
    }
    private function name(Test $test): string
    {
        if ($test->is_test_method()) {
            assert($test instanceof Test_Method);
            if (!$test->test_data()->has_data_from_data_provider()) {
                return $test->name_with_class();
            }
            return $test->class_name() . '::' . $test->method_name() . $test->test_data()->data_from_data_provider()->data_as_string_for_result_output();
        }
        return $test->name();
    }
    /**
     * @param array<string,list<ConsideredRisky|DeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpunitDeprecationTriggered|PhpunitErrorTriggered|PhpunitNoticeTriggered|PhpunitWarningTriggered|PhpWarningTriggered|WarningTriggered>> $events
     *
     * @return array{numberOfTestsWithIssues: int, numberOfIssues: int, elements: list<array{title: string, body: string}>}
     */
    private function map_tests_with_issues_events_to_elements(array $events): array
    {
        $elements = [];
        $issues = 0;
        foreach ($events as $reasons) {
            $test = $reasons[0]->test();
            $test_location = $this->test_location($test);
            $title = $this->name($test);
            $body = '';
            $first = true;
            $single = count($reasons) === 1;
            foreach ($reasons as $reason) {
                if ($first) {
                    $first = false;
                } else {
                    $body .= PHP_EOL;
                }
                $body .= $this->reason_message($reason, $single);
                $body .= $this->reason_location($reason, $single);
                $issues++;
            }
            if ($test_location !== '') {
                $body .= $test_location;
            }
            $elements[] = ['title' => $title, 'body' => $body];
        }
        return ['numberOfTestsWithIssues' => count($events), 'numberOfIssues' => $issues, 'elements' => $elements];
    }
    private function test_location(Test $test): string
    {
        if (!$test->is_test_method()) {
            return '';
        }
        assert($test instanceof Test_Method);
        return sprintf('%s%s:%d%s', PHP_EOL, $test->file(), $test->line(), PHP_EOL);
    }
    private function reason_message(Considered_Risky|Deprecation_Triggered|Error_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Phpunit_Deprecation_Triggered|Phpunit_Error_Triggered|Phpunit_Notice_Triggered|Phpunit_Warning_Triggered|Php_Warning_Triggered|Warning_Triggered $reason, bool $single): string
    {
        $message = trim($reason->message());
        if ($single) {
            return $message . PHP_EOL;
        }
        $lines = explode(PHP_EOL, $message);
        $buffer = '* ' . $lines[0] . PHP_EOL;
        if (count($lines) > 1) {
            foreach (range(1, count($lines) - 1) as $line) {
                $buffer .= '  ' . $lines[$line] . PHP_EOL;
            }
        }
        return $buffer;
    }
    private function reason_location(Considered_Risky|Deprecation_Triggered|Error_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Phpunit_Deprecation_Triggered|Phpunit_Error_Triggered|Phpunit_Notice_Triggered|Phpunit_Warning_Triggered|Php_Warning_Triggered|Warning_Triggered $reason, bool $single): string
    {
        if (!$reason instanceof Deprecation_Triggered && !$reason instanceof Php_Deprecation_Triggered && !$reason instanceof Error_Triggered && !$reason instanceof Notice_Triggered && !$reason instanceof Php_Notice_Triggered && !$reason instanceof Warning_Triggered && !$reason instanceof Php_Warning_Triggered) {
            return '';
        }
        return sprintf('%s%s:%d%s', $single ? '' : '  ', $reason->file(), $reason->line(), PHP_EOL);
    }
}