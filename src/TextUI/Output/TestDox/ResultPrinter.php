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
namespace Php_Unit\Text_Ui\Output\Test_Dox;

use function array_map;
use function explode;
use function implode;
use const PHP_EOL;
use Php_Unit\Event\Code\Throwable;
use Php_Unit\Event\Test\After_Last_Test_Method_Errored;
use Php_Unit\Event\Test\Before_First_Test_Method_Errored;
use Php_Unit\Framework\Test_Status\Test_Status;
use Php_Unit\Logging\Test_Dox\Test_Result as TestDoxTestResult;
use Php_Unit\Logging\Test_Dox\Test_Result_Collection;
use Php_Unit\Test_Runner\Test_Result\Test_Result;
use Php_Unit\Text_Ui\Output\Printer;
use Php_Unit\Util\Color;
use function preg_match;
use function preg_split;
use function rtrim;
use function sprintf;
use function str_starts_with;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Result_Printer
{
    public function __construct(private Printer $printer, private bool $colors, private int $columns, private bool $print_summary)
    {
    }
    /**
     * @param array<string, TestResultCollection> $tests
     */
    public function print(Test_Result $result, array $tests): void
    {
        $this->do_print($tests, false);
        if ($this->print_summary) {
            $this->printer->print('Summary of tests with errors, failures, or issues:' . PHP_EOL . PHP_EOL);
            $this->do_print($tests, true);
        }
        $before_first_test_method_errored = [];
        $after_last_test_method_errored = [];
        foreach ($result->test_errored_events() as $error) {
            if ($error instanceof Before_First_Test_Method_Errored) {
                $before_first_test_method_errored[$error->called_method()->class_name() . '::' . $error->called_method()->method_name()] = $error;
            }
            if ($error instanceof After_Last_Test_Method_Errored) {
                $after_last_test_method_errored[$error->called_method()->class_name() . '::' . $error->called_method()->method_name()] = $error;
            }
        }
        $this->print_before_class_or_after_class_errors('before-first-test', $before_first_test_method_errored);
        $this->print_before_class_or_after_class_errors('after-last-test', $after_last_test_method_errored);
    }
    /**
     * @param array<string, TestResultCollection> $tests
     */
    private function do_print(array $tests, bool $only_summary): void
    {
        foreach ($tests as $prettified_class_name => $_tests) {
            $print = true;
            if ($only_summary) {
                $found = false;
                foreach ($_tests as $test) {
                    if ($test->status()->is_success()) {
                        continue;
                    }
                    $found = true;
                    break;
                }
                if (!$found) {
                    $print = false;
                }
            }
            if (!$print) {
                continue;
            }
            $this->print_prettified_class_name($prettified_class_name);
            foreach ($_tests as $test) {
                if ($only_summary && $test->status()->is_success()) {
                    continue;
                }
                $this->print_test_result($test);
            }
            $this->printer->print(PHP_EOL);
        }
    }
    private function print_prettified_class_name(string $prettified_class_name): void
    {
        $buffer = $prettified_class_name;
        if ($this->colors) {
            $buffer = Color::colorize_text_box('underlined', $buffer);
        }
        $this->printer->print($buffer . PHP_EOL);
    }
    private function print_test_result(Test_Dox_Test_Result $test): void
    {
        $this->print_test_result_header($test);
        $this->print_test_result_body($test);
    }
    private function print_test_result_header(Test_Dox_Test_Result $test): void
    {
        $buffer = ' ' . $this->symbol_for($test->status()) . ' ';
        if ($this->colors) {
            $this->printer->print(Color::colorize_text_box($this->color_for($test->status()), $buffer));
        } else {
            $this->printer->print($buffer);
        }
        $this->printer->print($test->test()->test_dox()->prettified_method_name($this->colors) . PHP_EOL);
    }
    private function print_test_result_body(Test_Dox_Test_Result $test): void
    {
        if ($test->status()->is_success()) {
            return;
        }
        if (!$test->has_throwable()) {
            return;
        }
        $this->print_test_result_body_start($test);
        $this->print_throwable($test->status(), $test->throwable());
        $this->print_test_result_body_end($test);
    }
    private function print_test_result_body_start(Test_Dox_Test_Result $test): void
    {
        $this->printer->print($this->prefix_lines($this->prefix_for('start', $test->status()), ''));
        $this->printer->print(PHP_EOL);
    }
    private function print_test_result_body_end(Test_Dox_Test_Result $test): void
    {
        $this->printer->print(PHP_EOL);
        $this->printer->print($this->prefix_lines($this->prefix_for('last', $test->status()), ''));
        $this->printer->print(PHP_EOL);
    }
    private function print_throwable(Test_Status $status, Throwable $throwable): void
    {
        $message = trim($throwable->description());
        $stack_trace = $this->format_stack_trace($throwable->stack_trace());
        $diff = '';
        if ($message !== '' && $this->colors) {
            ['message' => $message, 'diff' => $diff] = $this->colorize_message_and_diff($message, $this->message_color_for($status));
        }
        if ($message !== '') {
            $this->printer->print($this->prefix_lines($this->prefix_for('message', $status), $message));
            $this->printer->print(PHP_EOL);
        }
        if ($diff !== '') {
            $this->printer->print($this->prefix_lines($this->prefix_for('diff', $status), $diff));
            $this->printer->print(PHP_EOL);
        }
        if ($stack_trace !== '') {
            if ($message !== '' || $diff !== '') {
                $trace_prefix = $this->prefix_for('default', $status);
            } else {
                $trace_prefix = $this->prefix_for('trace', $status);
            }
            $this->printer->print($this->prefix_lines($trace_prefix, PHP_EOL . $stack_trace));
        }
        if ($throwable->has_previous()) {
            $this->printer->print(PHP_EOL);
            $this->printer->print($this->prefix_lines($this->prefix_for('default', $status), ' '));
            $this->printer->print(PHP_EOL);
            $this->printer->print($this->prefix_lines($this->prefix_for('default', $status), 'Caused by:'));
            $this->printer->print(PHP_EOL);
            $this->print_throwable($status, $throwable->previous());
        }
    }
    /**
     * @return array{message: string, diff: string}
     */
    private function colorize_message_and_diff(string $buffer, string $style): array
    {
        $lines = [];
        if ($buffer !== '') {
            $lines = array_map(\rtrim(...), explode(PHP_EOL, $buffer));
        }
        $message = [];
        $diff = [];
        $inside_diff = false;
        foreach ($lines as $line) {
            if ($line === '--- Expected') {
                $inside_diff = true;
            }
            if (!$inside_diff) {
                $message[] = $line;
            } else {
                if (str_starts_with($line, '-')) {
                    $line = Color::colorize('fg-red', Color::visualize_whitespace($line, true));
                } elseif (str_starts_with($line, '+')) {
                    $line = Color::colorize('fg-green', Color::visualize_whitespace($line, true));
                } elseif ($line === '@@ @@') {
                    $line = Color::colorize('fg-cyan', $line);
                }
                $diff[] = $line;
            }
        }
        $message = implode(PHP_EOL, $message);
        $diff = implode(PHP_EOL, $diff);
        if ($message !== '') {
            // Testdox output has a left-margin of 5; keep right-margin to prevent terminal scrolling
            $message = Color::colorize_text_box($style, $message, $this->columns - 7);
        }
        return ['message' => $message, 'diff' => $diff];
    }
    private function format_stack_trace(string $stack_trace): string
    {
        if (!$this->colors) {
            return rtrim($stack_trace);
        }
        $lines = [];
        $previous_path = '';
        foreach (explode(PHP_EOL, $stack_trace) as $line) {
            if (preg_match('/^(.*):(\d+)$/', $line, $matches) > 0) {
                $lines[] = Color::colorize_path($matches[1], $previous_path) . Color::dim(':') . Color::colorize('fg-blue', $matches[2]) . "\n";
                $previous_path = $matches[1];
                continue;
            }
            $lines[] = $line;
            $previous_path = '';
        }
        return rtrim(implode('', $lines));
    }
    private function prefix_lines(string $prefix, string $message): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $message);
        if ($lines === false) {
            $lines = [];
        }
        return implode(PHP_EOL, array_map(static fn(string $line): string => '   ' . $prefix . ($line !== '' ? ' ' . $line : ''), $lines));
    }
    /**
     * @param 'default'|'diff'|'last'|'message'|'start'|'trace' $type
     */
    private function prefix_for(string $type, Test_Status $status): string
    {
        if (!$this->colors) {
            return '│';
        }
        return Color::colorize($this->color_for($status), match ($type) {
            'default' => '│',
            'start' => '┐',
            'message' => '├',
            'diff' => '┊',
            'trace' => '╵',
            'last' => '┴',
        });
    }
    private function color_for(Test_Status $status): string
    {
        if ($status->is_success()) {
            return 'fg-green';
        }
        if ($status->is_error()) {
            return 'fg-yellow';
        }
        if ($status->is_failure()) {
            return 'fg-red';
        }
        if ($status->is_skipped()) {
            return 'fg-cyan';
        }
        if ($status->is_incomplete() || $status->is_deprecation() || $status->is_notice() || $status->is_risky() || $status->is_warning()) {
            return 'fg-yellow';
        }
        return 'fg-blue';
    }
    private function message_color_for(Test_Status $status): string
    {
        if ($status->is_success()) {
            return '';
        }
        if ($status->is_error()) {
            return 'bg-yellow,fg-black';
        }
        if ($status->is_failure()) {
            return 'bg-red,fg-white';
        }
        if ($status->is_skipped()) {
            return 'fg-cyan';
        }
        if ($status->is_incomplete() || $status->is_deprecation() || $status->is_notice() || $status->is_risky() || $status->is_warning()) {
            return 'fg-yellow';
        }
        return 'fg-white,bg-blue';
    }
    private function symbol_for(Test_Status $status): string
    {
        if ($status->is_success()) {
            return '✔';
        }
        if ($status->is_error() || $status->is_failure()) {
            return '✘';
        }
        if ($status->is_skipped()) {
            return '↩';
        }
        if ($status->is_deprecation() || $status->is_notice() || $status->is_risky() || $status->is_warning()) {
            return '⚠';
        }
        if ($status->is_incomplete()) {
            return '∅';
        }
        return '?';
    }
    /**
     * @param 'after-last-test'|'before-first-test'                                            $type
     * @param array<non-empty-string, AfterLastTestMethodErrored|BeforeFirstTestMethodErrored> $errors
     */
    private function print_before_class_or_after_class_errors(string $type, array $errors): void
    {
        if ($errors === []) {
            return;
        }
        $this->printer->print(sprintf('These %s methods errored:' . PHP_EOL . PHP_EOL, $type));
        $index = 0;
        foreach ($errors as $method => $error) {
            $this->printer->print(sprintf('%d) %s' . PHP_EOL, ++$index, $method));
            $this->printer->print(trim($error->throwable()->description()) . PHP_EOL . PHP_EOL);
            $this->printer->print($this->format_stack_trace($error->throwable()->stack_trace()) . PHP_EOL);
        }
        $this->printer->print(PHP_EOL);
    }
}