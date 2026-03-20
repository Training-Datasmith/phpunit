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
namespace Php_Unit\Text_Ui\Xml_Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Php_Unit
{
    /**
     * @param array<non-empty-string, non-empty-string> $bootstrapForTestSuite
     * @param ?non-empty-string                         $extensionsDirectory
     * @param non-negative-int                          $shortenArraysForExportThreshold
     */
    public function __construct(private ?string $cache_directory, private bool $cache_result, private int|string $columns, private string $colors, private bool $stderr, private bool $display_details_on_all_issues, private bool $display_details_on_incomplete_tests, private bool $display_details_on_skipped_tests, private bool $display_details_on_tests_that_trigger_deprecations, private bool $display_details_on_phpunit_deprecations, private bool $display_details_on_phpunit_notices, private bool $display_details_on_tests_that_trigger_errors, private bool $display_details_on_tests_that_trigger_notices, private bool $display_details_on_tests_that_trigger_warnings, private bool $reverse_defect_list, private bool $require_coverage_metadata, private bool $require_sealed_mock_objects, private ?string $bootstrap, private array $bootstrap_for_test_suite, private bool $process_isolation, private bool $fail_on_all_issues, private bool $fail_on_deprecation, private bool $fail_on_phpunit_deprecation, private bool $fail_on_phpunit_notice, private bool $fail_on_phpunit_warning, private bool $fail_on_empty_test_suite, private bool $fail_on_incomplete, private bool $fail_on_notice, private bool $fail_on_risky, private bool $fail_on_skipped, private bool $fail_on_warning, private bool $stop_on_defect, private bool $stop_on_deprecation, private bool $stop_on_error, private bool $stop_on_failure, private bool $stop_on_incomplete, private bool $stop_on_notice, private bool $stop_on_risky, private bool $stop_on_skipped, private bool $stop_on_warning, private ?string $extensions_directory, private bool $be_strict_about_changes_to_global_state, private bool $be_strict_about_output_during_tests, private bool $be_strict_about_tests_that_do_not_test_anything, private bool $be_strict_about_coverage_metadata, private bool $enforce_time_limit, private int $default_time_limit, private int $timeout_for_small_tests, private int $timeout_for_medium_tests, private int $timeout_for_large_tests, private ?string $default_test_suite, private int $execution_order, private bool $resolve_dependencies, private bool $defects_first, private bool $backup_globals, private bool $backup_static_properties, private bool $testdox_printer, private bool $testdox_printer_summary, private bool $control_garbage_collector, private int $number_of_tests_before_garbage_collection, private int $shorten_arrays_for_export_threshold)
    {
    }
    /**
     * @phpstan-assert-if-true !null $this->cacheDirectory
     */
    public function has_cache_directory(): bool
    {
        return $this->cache_directory !== null;
    }
    /**
     * @throws Exception
     */
    public function cache_directory(): string
    {
        if (!$this->has_cache_directory()) {
            throw new Exception('Cache directory is not configured');
        }
        return $this->cache_directory;
    }
    public function cache_result(): bool
    {
        return $this->cache_result;
    }
    public function columns(): int|string
    {
        return $this->columns;
    }
    public function colors(): string
    {
        return $this->colors;
    }
    public function stderr(): bool
    {
        return $this->stderr;
    }
    public function display_details_on_all_issues(): bool
    {
        return $this->display_details_on_all_issues;
    }
    public function display_details_on_incomplete_tests(): bool
    {
        return $this->display_details_on_incomplete_tests;
    }
    public function display_details_on_skipped_tests(): bool
    {
        return $this->display_details_on_skipped_tests;
    }
    public function display_details_on_tests_that_trigger_deprecations(): bool
    {
        return $this->display_details_on_tests_that_trigger_deprecations;
    }
    public function display_details_on_phpunit_deprecations(): bool
    {
        return $this->display_details_on_phpunit_deprecations;
    }
    public function display_details_on_phpunit_notices(): bool
    {
        return $this->display_details_on_phpunit_notices;
    }
    public function display_details_on_tests_that_trigger_errors(): bool
    {
        return $this->display_details_on_tests_that_trigger_errors;
    }
    public function display_details_on_tests_that_trigger_notices(): bool
    {
        return $this->display_details_on_tests_that_trigger_notices;
    }
    public function display_details_on_tests_that_trigger_warnings(): bool
    {
        return $this->display_details_on_tests_that_trigger_warnings;
    }
    public function reverse_defect_list(): bool
    {
        return $this->reverse_defect_list;
    }
    public function require_coverage_metadata(): bool
    {
        return $this->require_coverage_metadata;
    }
    public function require_sealed_mock_objects(): bool
    {
        return $this->require_sealed_mock_objects;
    }
    /**
     * @phpstan-assert-if-true !null $this->bootstrap
     */
    public function has_bootstrap(): bool
    {
        return $this->bootstrap !== null;
    }
    /**
     * @throws Exception
     */
    public function bootstrap(): string
    {
        if (!$this->has_bootstrap()) {
            throw new Exception('Bootstrap script is not configured');
        }
        return $this->bootstrap;
    }
    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function bootstrap_for_test_suite(): array
    {
        return $this->bootstrap_for_test_suite;
    }
    public function process_isolation(): bool
    {
        return $this->process_isolation;
    }
    public function fail_on_all_issues(): bool
    {
        return $this->fail_on_all_issues;
    }
    public function fail_on_deprecation(): bool
    {
        return $this->fail_on_deprecation;
    }
    public function fail_on_phpunit_deprecation(): bool
    {
        return $this->fail_on_phpunit_deprecation;
    }
    public function fail_on_phpunit_notice(): bool
    {
        return $this->fail_on_phpunit_notice;
    }
    public function fail_on_phpunit_warning(): bool
    {
        return $this->fail_on_phpunit_warning;
    }
    public function fail_on_empty_test_suite(): bool
    {
        return $this->fail_on_empty_test_suite;
    }
    public function fail_on_incomplete(): bool
    {
        return $this->fail_on_incomplete;
    }
    public function fail_on_notice(): bool
    {
        return $this->fail_on_notice;
    }
    public function fail_on_risky(): bool
    {
        return $this->fail_on_risky;
    }
    public function fail_on_skipped(): bool
    {
        return $this->fail_on_skipped;
    }
    public function fail_on_warning(): bool
    {
        return $this->fail_on_warning;
    }
    public function stop_on_defect(): bool
    {
        return $this->stop_on_defect;
    }
    public function stop_on_deprecation(): bool
    {
        return $this->stop_on_deprecation;
    }
    public function stop_on_error(): bool
    {
        return $this->stop_on_error;
    }
    public function stop_on_failure(): bool
    {
        return $this->stop_on_failure;
    }
    public function stop_on_incomplete(): bool
    {
        return $this->stop_on_incomplete;
    }
    public function stop_on_notice(): bool
    {
        return $this->stop_on_notice;
    }
    public function stop_on_risky(): bool
    {
        return $this->stop_on_risky;
    }
    public function stop_on_skipped(): bool
    {
        return $this->stop_on_skipped;
    }
    public function stop_on_warning(): bool
    {
        return $this->stop_on_warning;
    }
    /**
     * @phpstan-assert-if-true !null $this->extensionsDirectory
     */
    public function has_extensions_directory(): bool
    {
        return $this->extensions_directory !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-string
     */
    public function extensions_directory(): string
    {
        if (!$this->has_extensions_directory()) {
            throw new Exception('Extensions directory is not configured');
        }
        return $this->extensions_directory;
    }
    public function be_strict_about_changes_to_global_state(): bool
    {
        return $this->be_strict_about_changes_to_global_state;
    }
    public function be_strict_about_output_during_tests(): bool
    {
        return $this->be_strict_about_output_during_tests;
    }
    public function be_strict_about_tests_that_do_not_test_anything(): bool
    {
        return $this->be_strict_about_tests_that_do_not_test_anything;
    }
    public function be_strict_about_coverage_metadata(): bool
    {
        return $this->be_strict_about_coverage_metadata;
    }
    public function enforce_time_limit(): bool
    {
        return $this->enforce_time_limit;
    }
    public function default_time_limit(): int
    {
        return $this->default_time_limit;
    }
    public function timeout_for_small_tests(): int
    {
        return $this->timeout_for_small_tests;
    }
    public function timeout_for_medium_tests(): int
    {
        return $this->timeout_for_medium_tests;
    }
    public function timeout_for_large_tests(): int
    {
        return $this->timeout_for_large_tests;
    }
    /**
     * @phpstan-assert-if-true !null $this->defaultTestSuite
     */
    public function has_default_test_suite(): bool
    {
        return $this->default_test_suite !== null;
    }
    /**
     * @throws Exception
     */
    public function default_test_suite(): string
    {
        if (!$this->has_default_test_suite()) {
            throw new Exception('Default test suite is not configured');
        }
        return $this->default_test_suite;
    }
    public function execution_order(): int
    {
        return $this->execution_order;
    }
    public function resolve_dependencies(): bool
    {
        return $this->resolve_dependencies;
    }
    public function defects_first(): bool
    {
        return $this->defects_first;
    }
    public function backup_globals(): bool
    {
        return $this->backup_globals;
    }
    public function backup_static_properties(): bool
    {
        return $this->backup_static_properties;
    }
    public function testdox_printer(): bool
    {
        return $this->testdox_printer;
    }
    public function testdox_printer_summary(): bool
    {
        return $this->testdox_printer_summary;
    }
    public function control_garbage_collector(): bool
    {
        return $this->control_garbage_collector;
    }
    public function number_of_tests_before_garbage_collection(): int
    {
        return $this->number_of_tests_before_garbage_collection;
    }
    /**
     * @return non-negative-int
     */
    public function shorten_arrays_for_export_threshold(): int
    {
        return $this->shorten_arrays_for_export_threshold;
    }
}