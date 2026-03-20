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

use function assert;
use const PHP_EOL;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Logging\Team_City\Team_City_Logger;
use Php_Unit\Logging\Test_Dox\Test_Result_Collection;
use Php_Unit\Runner\Directory_Does_Not_Exist_Exception;
use Php_Unit\Test_Runner\Test_Result\Test_Result;
use Php_Unit\Text_Ui\Cannot_Open_Socket_Exception;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Invalid_Socket_Exception;
use Php_Unit\Text_Ui\Output\Default\Progress_Printer\Progress_Printer as DefaultProgressPrinter;
use Php_Unit\Text_Ui\Output\Default\Result_Printer as DefaultResultPrinter;
use Php_Unit\Text_Ui\Output\Default\Unexpected_Output_Printer;
use Php_Unit\Text_Ui\Output\Test_Dox\Result_Printer as TestDoxResultPrinter;
use Sebastian_Bergmann\Timer\Duration;
use Sebastian_Bergmann\Timer\Resource_Usage_Formatter;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Printer $printer = null;
    private static ?Default_Result_Printer $default_result_printer = null;
    private static ?Test_Dox_Result_Printer $test_dox_result_printer = null;
    private static ?Summary_Printer $summary_printer = null;
    private static bool $default_progress_printer = false;
    public static function init(Configuration $configuration, bool $extension_replaces_progress_output, bool $extension_replaces_result_output): Printer
    {
        self::create_printer($configuration);
        assert(self::$printer !== null);
        if ($configuration->debug()) {
            return self::$printer;
        }
        self::create_unexpected_output_printer();
        if (!$extension_replaces_progress_output) {
            self::create_progress_printer($configuration);
        }
        if (!$extension_replaces_result_output) {
            self::create_result_printer($configuration);
            self::create_summary_printer($configuration);
        }
        if ($configuration->output_is_team_city()) {
            new Team_City_Logger(Default_Printer::standard_output(), Event_Facade::instance());
        }
        assert(self::$printer !== null);
        return self::$printer;
    }
    /**
     * @param ?array<string, TestResultCollection> $testDoxResult
     */
    public static function print_result(Test_Result $result, ?array $test_dox_result, Duration $duration, bool $stack_trace_for_deprecations): void
    {
        assert(self::$printer !== null);
        if ($result->number_of_tests_run() > 0) {
            if (self::$default_progress_printer) {
                self::$printer->print(PHP_EOL . PHP_EOL);
            }
            self::$printer->print((new Resource_Usage_Formatter())->resource_usage($duration) . PHP_EOL . PHP_EOL);
        }
        if (self::$test_dox_result_printer !== null && $test_dox_result !== null) {
            self::$test_dox_result_printer->print($result, $test_dox_result);
        }
        if (self::$default_result_printer !== null) {
            self::$default_result_printer->print($result, $stack_trace_for_deprecations);
        }
        if (self::$summary_printer !== null) {
            self::$summary_printer->print($result);
        }
    }
    /**
     * @throws CannotOpenSocketException
     * @throws DirectoryDoesNotExistException
     * @throws InvalidSocketException
     */
    public static function printer_for(string $target): Printer
    {
        if ($target === 'php://stdout') {
            if (self::$printer !== null && !self::$printer instanceof Null_Printer) {
                return self::$printer;
            }
            return Default_Printer::standard_output();
        }
        return Default_Printer::from($target);
    }
    private static function create_printer(Configuration $configuration): void
    {
        $printer_needed = false;
        if ($configuration->debug()) {
            $printer_needed = true;
        }
        if ($configuration->output_is_team_city()) {
            $printer_needed = true;
        }
        if ($configuration->output_is_test_dox()) {
            $printer_needed = true;
        }
        if (!$configuration->no_output() && !$configuration->no_progress()) {
            $printer_needed = true;
        }
        if (!$configuration->no_output() && !$configuration->no_results()) {
            $printer_needed = true;
        }
        if ($printer_needed) {
            if ($configuration->output_to_standard_error_stream()) {
                self::$printer = Default_Printer::standard_error();
                return;
            }
            self::$printer = Default_Printer::standard_output();
            return;
        }
        self::$printer = new Null_Printer();
    }
    private static function create_progress_printer(Configuration $configuration): void
    {
        assert(self::$printer !== null);
        if (!self::use_default_progress_printer($configuration)) {
            return;
        }
        new Default_Progress_Printer(self::$printer, Event_Facade::instance(), $configuration->colors(), $configuration->columns(), $configuration->source());
        self::$default_progress_printer = true;
    }
    private static function use_default_progress_printer(Configuration $configuration): bool
    {
        if ($configuration->no_output()) {
            return false;
        }
        if ($configuration->no_progress()) {
            return false;
        }
        if ($configuration->output_is_team_city()) {
            return false;
        }
        return true;
    }
    private static function create_result_printer(Configuration $configuration): void
    {
        assert(self::$printer !== null);
        if ($configuration->output_is_test_dox()) {
            self::$default_result_printer = new Default_Result_Printer(self::$printer, $configuration->display_details_on_phpunit_deprecations() || $configuration->display_details_on_all_issues(), true, $configuration->display_details_on_phpunit_notices() || $configuration->display_details_on_all_issues(), true, false, false, true, false, false, $configuration->display_details_on_tests_that_trigger_deprecations() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_errors() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_notices() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_warnings() || $configuration->display_details_on_all_issues(), $configuration->reverse_defect_list());
        }
        if ($configuration->output_is_test_dox()) {
            self::$test_dox_result_printer = new Test_Dox_Result_Printer(self::$printer, $configuration->colors(), $configuration->columns(), $configuration->test_dox_output_with_summary());
        }
        if ($configuration->no_output() || $configuration->no_results()) {
            return;
        }
        if (self::$default_result_printer !== null) {
            return;
        }
        self::$default_result_printer = new Default_Result_Printer(self::$printer, $configuration->display_details_on_phpunit_deprecations() || $configuration->display_details_on_all_issues(), true, $configuration->display_details_on_phpunit_notices() || $configuration->display_details_on_all_issues(), true, true, true, true, $configuration->display_details_on_incomplete_tests() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_skipped_tests() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_deprecations() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_errors() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_notices() || $configuration->display_details_on_all_issues(), $configuration->display_details_on_tests_that_trigger_warnings() || $configuration->display_details_on_all_issues(), $configuration->reverse_defect_list());
    }
    private static function create_summary_printer(Configuration $configuration): void
    {
        assert(self::$printer !== null);
        if (($configuration->no_output() || $configuration->no_results()) && !($configuration->output_is_team_city() || $configuration->output_is_test_dox())) {
            return;
        }
        self::$summary_printer = new Summary_Printer(self::$printer, $configuration->colors());
    }
    private static function create_unexpected_output_printer(): void
    {
        assert(self::$printer !== null);
        new Unexpected_Output_Printer(self::$printer, Event_Facade::instance());
    }
}