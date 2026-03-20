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
namespace Php_Unit\Text_Ui\Output\Default\Progress_Printer;

use function floor;
use Php_Unit\Event\Facade;
use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Error_Triggered;
use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Php_Deprecation_Triggered;
use Php_Unit\Event\Test\Php_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Warning_Triggered;
use Php_Unit\Event\Test\Php_Warning_Triggered;
use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Event\Test_Runner\Execution_Started;
use Php_Unit\Framework\Test_Status\Test_Status;
use Php_Unit\Text_Ui\Configuration\Source;
use Php_Unit\Text_Ui\Configuration\Source_Filter;
use Php_Unit\Text_Ui\Output\Printer;
use Php_Unit\Util\Color;
use function sprintf;
use function str_repeat;
use function strlen;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Progress_Printer
{
    private int $column = 0;
    private int $number_of_tests = 0;
    private int $number_of_tests_width = 0;
    private int $max_column = 0;
    private int $number_of_tests_run = 0;
    private ?Test_Status $status = null;
    private bool $prepared = false;
    private bool $child_process_errored = false;
    public function __construct(private readonly Printer $printer, Facade $facade, private readonly bool $colors, private readonly int $number_of_columns, private readonly Source $source)
    {
        $this->register_subscribers($facade);
    }
    public function test_runner_execution_started(Execution_Started $event): void
    {
        $this->number_of_tests_run = 0;
        $this->number_of_tests = $event->test_suite()->count();
        $this->number_of_tests_width = strlen((string) $this->number_of_tests);
        $this->column = 0;
        $this->max_column = $this->number_of_columns - strlen('  /  (XXX%)') - 2 * $this->number_of_tests_width;
    }
    public function before_test_class_method_errored(): void
    {
        $this->print_progress_for_error();
        $this->update_test_status(Test_Status::error());
    }
    public function test_prepared(): void
    {
        $this->prepared = true;
    }
    public function test_skipped(): void
    {
        if (!$this->prepared) {
            $this->print_progress_for_skipped();
        } else {
            $this->update_test_status(Test_Status::skipped());
        }
    }
    public function test_suite_skipped(int $count_tests): void
    {
        for ($i = 0; $i < $count_tests; $i++) {
            $this->test_skipped();
        }
    }
    public function test_marked_incomplete(): void
    {
        $this->update_test_status(Test_Status::incomplete());
    }
    public function test_triggered_notice(Notice_Triggered $event): void
    {
        if ($event->ignored_by_baseline()) {
            return;
        }
        if ($this->source->restrict_notices() && !Source_Filter::instance()->includes($event->file())) {
            return;
        }
        if (!$this->source->ignore_suppression_of_notices() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::notice());
    }
    public function test_triggered_php_notice(Php_Notice_Triggered $event): void
    {
        if ($event->ignored_by_baseline()) {
            return;
        }
        if ($this->source->restrict_notices() && !Source_Filter::instance()->includes($event->file())) {
            return;
        }
        if (!$this->source->ignore_suppression_of_php_notices() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::notice());
    }
    public function test_triggered_deprecation(Deprecation_Triggered $event): void
    {
        if ($event->ignored_by_baseline() || $event->ignored_by_test()) {
            return;
        }
        if ($this->source->ignore_self_deprecations() && $event->trigger()->is_self()) {
            return;
        }
        if ($this->source->ignore_direct_deprecations() && $event->trigger()->is_direct()) {
            return;
        }
        if ($this->source->ignore_indirect_deprecations() && $event->trigger()->is_indirect()) {
            return;
        }
        if (!$this->source->ignore_suppression_of_deprecations() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::deprecation());
    }
    public function test_triggered_php_deprecation(Php_Deprecation_Triggered $event): void
    {
        if ($event->ignored_by_baseline() || $event->ignored_by_test()) {
            return;
        }
        if ($this->source->ignore_self_deprecations() && $event->trigger()->is_self()) {
            return;
        }
        if ($this->source->ignore_direct_deprecations() && $event->trigger()->is_direct()) {
            return;
        }
        if ($this->source->ignore_indirect_deprecations() && $event->trigger()->is_indirect()) {
            return;
        }
        if (!$this->source->ignore_suppression_of_php_deprecations() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::deprecation());
    }
    public function test_triggered_phpunit_deprecation(): void
    {
        $this->update_test_status(Test_Status::deprecation());
    }
    public function test_triggered_phpunit_notice(): void
    {
        $this->update_test_status(Test_Status::notice());
    }
    public function test_considered_risky(): void
    {
        $this->update_test_status(Test_Status::risky());
    }
    public function test_triggered_warning(Warning_Triggered $event): void
    {
        if ($event->ignored_by_baseline()) {
            return;
        }
        if ($this->source->restrict_warnings() && !Source_Filter::instance()->includes($event->file())) {
            return;
        }
        if (!$this->source->ignore_suppression_of_warnings() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::warning());
    }
    public function test_triggered_php_warning(Php_Warning_Triggered $event): void
    {
        if ($event->ignored_by_baseline()) {
            return;
        }
        if ($this->source->restrict_warnings() && !Source_Filter::instance()->includes($event->file())) {
            return;
        }
        if (!$this->source->ignore_suppression_of_php_warnings() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::warning());
    }
    public function test_triggered_phpunit_warning(Phpunit_Warning_Triggered $event): void
    {
        if ($event->ignored_by_test()) {
            return;
        }
        $this->update_test_status(Test_Status::warning());
    }
    public function test_triggered_error(Error_Triggered $event): void
    {
        if (!$this->source->ignore_suppression_of_errors() && $event->was_suppressed()) {
            return;
        }
        $this->update_test_status(Test_Status::error());
    }
    public function test_failed(): void
    {
        $this->update_test_status(Test_Status::failure());
    }
    public function test_errored(): void
    {
        if ($this->child_process_errored) {
            $this->update_test_status(Test_Status::error());
            return;
        }
        if (!$this->prepared) {
            $this->print_progress_for_error();
        } else {
            $this->update_test_status(Test_Status::error());
        }
    }
    public function test_finished(): void
    {
        if ($this->status === null) {
            $this->print_progress_for_success();
        } elseif ($this->status->is_skipped()) {
            $this->print_progress_for_skipped();
        } elseif ($this->status->is_incomplete()) {
            $this->print_progress_for_incomplete();
        } elseif ($this->status->is_risky()) {
            $this->print_progress_for_risky();
        } elseif ($this->status->is_notice()) {
            $this->print_progress_for_notice();
        } elseif ($this->status->is_deprecation()) {
            $this->print_progress_for_deprecation();
        } elseif ($this->status->is_warning()) {
            $this->print_progress_for_warning();
        } elseif ($this->status->is_failure()) {
            $this->print_progress_for_failure();
        } else {
            $this->print_progress_for_error();
        }
        $this->status = null;
        $this->prepared = false;
        $this->child_process_errored = false;
    }
    public function child_process_errored(): void
    {
        $this->child_process_errored = true;
    }
    private function register_subscribers(Facade $facade): void
    {
        $facade->register_subscribers(new Before_Test_Class_Method_Errored_Subscriber($this), new Test_Considered_Risky_Subscriber($this), new Test_Errored_Subscriber($this), new Test_Failed_Subscriber($this), new Test_Finished_Subscriber($this), new Test_Marked_Incomplete_Subscriber($this), new Test_Prepared_Subscriber($this), new Test_Runner_Execution_Started_Subscriber($this), new Test_Skipped_Subscriber($this), new Test_Suite_Skipped_Subscriber($this), new Test_Triggered_Deprecation_Subscriber($this), new Test_Triggered_Notice_Subscriber($this), new Test_Triggered_Php_Deprecation_Subscriber($this), new Test_Triggered_Php_Notice_Subscriber($this), new Test_Triggered_Phpunit_Deprecation_Subscriber($this), new Test_Triggered_Phpunit_Notice_Subscriber($this), new Test_Triggered_Phpunit_Warning_Subscriber($this), new Test_Triggered_Php_Warning_Subscriber($this), new Test_Triggered_Warning_Subscriber($this), new Child_Process_Errored_Subscriber($this));
    }
    private function update_test_status(Test_Status $status): void
    {
        if ($this->status !== null && $this->status->is_more_important_than($status)) {
            return;
        }
        $this->status = $status;
    }
    private function print_progress_for_success(): void
    {
        $this->print_progress('.');
    }
    private function print_progress_for_skipped(): void
    {
        $this->print_progress_with_color('fg-cyan, bold', 'S');
    }
    private function print_progress_for_incomplete(): void
    {
        $this->print_progress_with_color('fg-yellow, bold', 'I');
    }
    private function print_progress_for_notice(): void
    {
        $this->print_progress_with_color('fg-yellow, bold', 'N');
    }
    private function print_progress_for_deprecation(): void
    {
        $this->print_progress_with_color('fg-yellow, bold', 'D');
    }
    private function print_progress_for_risky(): void
    {
        $this->print_progress_with_color('fg-yellow, bold', 'R');
    }
    private function print_progress_for_warning(): void
    {
        $this->print_progress_with_color('fg-yellow, bold', 'W');
    }
    private function print_progress_for_failure(): void
    {
        $this->print_progress_with_color('bg-red, fg-white', 'F');
    }
    private function print_progress_for_error(): void
    {
        $this->print_progress_with_color('fg-red, bold', 'E');
    }
    private function print_progress_with_color(string $color, string $progress): void
    {
        if ($this->colors) {
            $progress = Color::colorize_text_box($color, $progress);
        }
        $this->print_progress($progress);
    }
    private function print_progress(string $progress): void
    {
        $this->printer->print($progress);
        $this->column++;
        $this->number_of_tests_run++;
        if ($this->column === $this->max_column || $this->number_of_tests_run === $this->number_of_tests) {
            if ($this->number_of_tests_run === $this->number_of_tests) {
                $this->printer->print(str_repeat(' ', $this->max_column - $this->column));
            }
            $this->printer->print(sprintf(' %' . $this->number_of_tests_width . 'd / %' . $this->number_of_tests_width . 'd (%3s%%)', $this->number_of_tests_run, $this->number_of_tests, floor($this->number_of_tests_run / $this->number_of_tests * 100)));
            if ($this->column === $this->max_column) {
                $this->column = 0;
                $this->printer->print("\n");
            }
        }
    }
}