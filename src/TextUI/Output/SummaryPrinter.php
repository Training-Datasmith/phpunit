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
namespace Php_Unit\Text_Ui\Output;

use const PHP_EOL;
use Php_Unit\Test_Runner\Test_Result\Test_Result;
use Php_Unit\Util\Color;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Summary_Printer
{
    private bool $count_printed = false;
    public function __construct(private readonly Printer $printer, private readonly bool $colors)
    {
    }
    public function print(Test_Result $result): void
    {
        if ($result->number_of_tests_run() === 0) {
            $this->print_with_color('fg-black, bg-yellow', 'No tests executed!');
            return;
        }
        if ($result->was_successful() && !$result->has_issues() && !$result->has_test_suite_skipped_events() && !$result->has_test_skipped_events()) {
            $this->print_with_color('fg-black, bg-green', sprintf('OK (%d test%s, %d assertion%s)', $result->number_of_tests_run(), $result->number_of_tests_run() === 1 ? '' : 's', $result->number_of_assertions(), $result->number_of_assertions() === 1 ? '' : 's'));
            $this->print_number_of_issues_ignored_by_baseline($result);
            return;
        }
        if ($result->was_successful()) {
            if ($result->has_issues()) {
                $color = 'fg-black, bg-yellow';
                $this->print_with_color($color, 'OK, but there were issues!');
            } else {
                $color = 'fg-black, bg-green';
                $this->print_with_color($color, 'OK, but some tests were skipped!');
            }
        } else {
            $color = 'fg-white, bg-red';
            if ($result->has_test_errored_events() || $result->has_test_triggered_phpunit_error_events()) {
                $this->print_with_color('fg-white, bg-red', 'ERRORS!');
            } else {
                $this->print_with_color('fg-white, bg-red', 'FAILURES!');
            }
        }
        $this->print_count_string($result->number_of_tests_run(), 'Tests', $color, true);
        $this->print_count_string($result->number_of_assertions(), 'Assertions', $color, true);
        $this->print_count_string($result->number_of_errors(), 'Errors', $color);
        $this->print_count_string($result->number_of_test_failed_events(), 'Failures', $color);
        $this->print_count_string($result->number_of_phpunit_warnings(), 'PHPUnit Warnings', $color);
        $this->print_count_string($result->number_of_warnings(), 'Warnings', $color);
        $this->print_count_string($result->number_of_php_or_user_deprecations(), 'Deprecations', $color);
        $this->print_count_string($result->number_of_phpunit_deprecations(), 'PHPUnit Deprecations', $color);
        $this->print_count_string($result->number_of_phpunit_notices(), 'PHPUnit Notices', $color);
        $this->print_count_string($result->number_of_notices(), 'Notices', $color);
        $this->print_count_string($result->number_of_test_skipped_by_test_suite_skipped_events() + $result->number_of_test_skipped_events(), 'Skipped', $color);
        $this->print_count_string($result->number_of_test_marked_incomplete_events(), 'Incomplete', $color);
        $this->print_count_string($result->number_of_tests_with_test_considered_risky_events(), 'Risky', $color);
        $this->print_with_color($color, '.');
        $this->print_number_of_issues_ignored_by_baseline($result);
    }
    private function print_count_string(int $count, string $name, string $color, bool $always = false): void
    {
        if ($always || $count > 0) {
            $this->print_with_color($color, sprintf('%s%s: %d', $this->count_printed ? ', ' : '', $name, $count), false);
            $this->count_printed = true;
        }
    }
    private function print_with_color(string $color, string $buffer, bool $lf = true): void
    {
        if ($this->colors) {
            $buffer = Color::colorize_text_box($color, $buffer);
        }
        $this->printer->print($buffer);
        if ($lf) {
            $this->printer->print(PHP_EOL);
        }
    }
    private function print_number_of_issues_ignored_by_baseline(Test_Result $result): void
    {
        if ($result->has_issues_ignored_by_baseline()) {
            $this->printer->print(sprintf('%s%d issue%s %s ignored by baseline.%s', PHP_EOL, $result->number_of_issues_ignored_by_baseline(), $result->number_of_issues_ignored_by_baseline() > 1 ? 's' : '', $result->number_of_issues_ignored_by_baseline() > 1 ? 'were' : 'was', PHP_EOL));
        }
    }
}