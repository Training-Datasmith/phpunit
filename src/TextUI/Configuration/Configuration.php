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
namespace Php_Unit\Text_Ui\Configuration;

use function explode;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Configuration
{
    public const string COLOR_NEVER = 'never';
    public const string COLOR_AUTO = 'auto';
    public const string COLOR_ALWAYS = 'always';
    public const string COLOR_DEFAULT = self::COLOR_NEVER;
    /**
     * @param list<non-empty-string>                                                      $cliArguments
     * @param array<non-empty-string, non-empty-string>                                   $bootstrapForTestSuite
     * @param ?non-empty-string                                                           $pharExtensionDirectory
     * @param list<array{className: non-empty-string, parameters: array<string, string>}> $extensionBootstrappers
     * @param ?non-empty-list<non-empty-string>                                           $testsCovering
     * @param ?non-empty-list<non-empty-string>                                           $testsUsing
     * @param ?non-empty-list<non-empty-string>                                           $testsRequiringPhpExtension
     * @param list<non-empty-string>                                                      $groups
     * @param list<non-empty-string>                                                      $excludeGroups
     * @param non-empty-list<non-empty-string>                                            $testSuffixes
     * @param null|non-empty-string                                                       $generateBaseline
     * @param non-negative-int                                                            $shortenArraysForExportThreshold
     */
    public function __construct(private array $cli_arguments, private ?string $test_files_file, private ?string $configuration_file, private ?string $bootstrap, private array $bootstrap_for_test_suite, private bool $cache_result, private ?string $cache_directory, private ?string $coverage_cache_directory, private Source $source, private string $test_result_cache_file, private ?string $coverage_clover, private ?string $coverage_cobertura, private ?string $coverage_crap4j, private int $coverage_crap4j_threshold, private ?string $coverage_html, private int $coverage_html_low_upper_bound, private int $coverage_html_high_lower_bound, private string $coverage_html_color_success_low, private string $coverage_html_color_success_low_dark, private string $coverage_html_color_success_medium, private string $coverage_html_color_success_medium_dark, private string $coverage_html_color_success_high, private string $coverage_html_color_success_high_dark, private string $coverage_html_color_success_bar, private string $coverage_html_color_success_bar_dark, private string $coverage_html_color_warning, private string $coverage_html_color_warning_dark, private string $coverage_html_color_warning_bar, private string $coverage_html_color_warning_bar_dark, private string $coverage_html_color_danger, private string $coverage_html_color_danger_dark, private string $coverage_html_color_danger_bar, private string $coverage_html_color_danger_bar_dark, private string $coverage_html_color_breadcrumbs, private string $coverage_html_color_breadcrumbs_dark, private ?string $coverage_html_custom_css_file, private ?string $coverage_open_clover, private ?string $coverage_php, private ?string $coverage_text, private bool $coverage_text_show_uncovered_files, private bool $coverage_text_show_only_summary, private ?string $coverage_xml, private bool $coverage_xml_include_source, private bool $path_coverage, private bool $ignore_deprecated_code_units_from_code_coverage, private bool $disable_code_coverage_ignore, private bool $fail_on_all_issues, private bool $fail_on_deprecation, private bool $fail_on_phpunit_deprecation, private bool $fail_on_phpunit_notice, private bool $fail_on_phpunit_warning, private bool $fail_on_empty_test_suite, private bool $fail_on_incomplete, private bool $fail_on_notice, private bool $fail_on_risky, private bool $fail_on_skipped, private bool $fail_on_warning, private bool $do_not_fail_on_deprecation, private bool $do_not_fail_on_phpunit_deprecation, private bool $do_not_fail_on_phpunit_notice, private bool $do_not_fail_on_phpunit_warning, private bool $do_not_fail_on_empty_test_suite, private bool $do_not_fail_on_incomplete, private bool $do_not_fail_on_notice, private bool $do_not_fail_on_risky, private bool $do_not_fail_on_skipped, private bool $do_not_fail_on_warning, private bool $stop_on_defect, private bool $stop_on_deprecation, private ?string $specific_deprecation_to_stop_on, private bool $stop_on_error, private bool $stop_on_failure, private bool $stop_on_incomplete, private bool $stop_on_notice, private bool $stop_on_risky, private bool $stop_on_skipped, private bool $stop_on_warning, private bool $output_to_standard_error_stream, private int $columns, private bool $no_extensions, private ?string $phar_extension_directory, private array $extension_bootstrappers, private bool $backup_globals, private bool $backup_static_properties, private bool $be_strict_about_changes_to_global_state, private bool $colors, private bool $process_isolation, private bool $enforce_time_limit, private int $default_time_limit, private int $timeout_for_small_tests, private int $timeout_for_medium_tests, private int $timeout_for_large_tests, private bool $report_useless_tests, private bool $strict_coverage, private bool $disallow_test_output, private bool $display_details_on_all_issues, private bool $display_details_on_incomplete_tests, private bool $display_details_on_skipped_tests, private bool $display_details_on_tests_that_trigger_deprecations, private bool $display_details_on_phpunit_deprecations, private bool $display_details_on_phpunit_notices, private bool $display_details_on_tests_that_trigger_errors, private bool $display_details_on_tests_that_trigger_notices, private bool $display_details_on_tests_that_trigger_warnings, private bool $reverse_defect_list, private bool $require_coverage_metadata, private bool $require_sealed_mock_objects, private bool $no_progress, private bool $no_results, private bool $no_output, private int $execution_order, private int $execution_order_defects, private bool $resolve_dependencies, private ?string $logfile_teamcity, private ?string $logfile_junit, private ?string $logfile_otr, private bool $include_git_information, private bool $include_git_information_in_otr_logfile, private ?string $logfile_testdox_html, private ?string $logfile_testdox_text, private ?string $log_events_text, private ?string $log_events_verbose_text, private bool $team_city_output, private bool $test_dox_output, private bool $test_dox_output_summary, private ?array $tests_covering, private ?array $tests_using, private ?array $tests_requiring_php_extension, private ?string $filter, private ?string $exclude_filter, private array $groups, private array $exclude_groups, private int $random_order_seed, private bool $include_uncovered_files, private Test_Suite_Collection $test_suite, private string $include_test_suite, private string $exclude_test_suite, private ?string $default_test_suite, private bool $ignore_test_selection_in_xml_configuration, private array $test_suffixes, private Php $php, private bool $control_garbage_collector, private int $number_of_tests_before_garbage_collection, private ?string $generate_baseline, private bool $debug, private bool $with_telemetry, private int $shorten_arrays_for_export_threshold)
    {
    }
    /**
     * @phpstan-assert-if-true !empty $this->cliArguments
     */
    public function has_cli_arguments(): bool
    {
        return $this->cli_arguments !== [];
    }
    /**
     * @return list<non-empty-string>
     */
    public function cli_arguments(): array
    {
        return $this->cli_arguments;
    }
    /**
     * @phpstan-assert-if-true !null $this->testFilesFile
     */
    public function has_test_files_file(): bool
    {
        return $this->test_files_file !== null;
    }
    /**
     * @throws NoTestFilesFileException
     */
    public function test_files_file(): string
    {
        if (!$this->has_test_files_file()) {
            throw new No_Test_Files_File_Exception();
        }
        return $this->test_files_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->configurationFile
     */
    public function has_configuration_file(): bool
    {
        return $this->configuration_file !== null;
    }
    /**
     * @throws NoConfigurationFileException
     */
    public function configuration_file(): string
    {
        if (!$this->has_configuration_file()) {
            throw new No_Configuration_File_Exception();
        }
        return $this->configuration_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->bootstrap
     */
    public function has_bootstrap(): bool
    {
        return $this->bootstrap !== null;
    }
    /**
     * @throws NoBootstrapException
     */
    public function bootstrap(): string
    {
        if (!$this->has_bootstrap()) {
            throw new No_Bootstrap_Exception();
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
    public function cache_result(): bool
    {
        return $this->cache_result;
    }
    /**
     * @phpstan-assert-if-true !null $this->cacheDirectory
     */
    public function has_cache_directory(): bool
    {
        return $this->cache_directory !== null;
    }
    /**
     * @throws NoCacheDirectoryException
     */
    public function cache_directory(): string
    {
        if (!$this->has_cache_directory()) {
            throw new No_Cache_Directory_Exception();
        }
        return $this->cache_directory;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageCacheDirectory
     */
    public function has_coverage_cache_directory(): bool
    {
        return $this->coverage_cache_directory !== null;
    }
    /**
     * @throws NoCoverageCacheDirectoryException
     */
    public function coverage_cache_directory(): string
    {
        if (!$this->has_coverage_cache_directory()) {
            throw new No_Coverage_Cache_Directory_Exception();
        }
        return $this->coverage_cache_directory;
    }
    public function source(): Source
    {
        return $this->source;
    }
    public function test_result_cache_file(): string
    {
        return $this->test_result_cache_file;
    }
    public function ignore_deprecated_code_units_from_code_coverage(): bool
    {
        return $this->ignore_deprecated_code_units_from_code_coverage;
    }
    public function disable_code_coverage_ignore(): bool
    {
        return $this->disable_code_coverage_ignore;
    }
    public function path_coverage(): bool
    {
        return $this->path_coverage;
    }
    public function has_coverage_report(): bool
    {
        if ($this->has_coverage_clover()) {
            return true;
        }
        if ($this->has_coverage_cobertura()) {
            return true;
        }
        if ($this->has_coverage_crap4j()) {
            return true;
        }
        if ($this->has_coverage_html()) {
            return true;
        }
        if ($this->has_coverage_open_clover()) {
            return true;
        }
        if ($this->has_coverage_php()) {
            return true;
        }
        if ($this->has_coverage_text()) {
            return true;
        }
        return $this->has_coverage_xml();
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageClover
     */
    public function has_coverage_clover(): bool
    {
        return $this->coverage_clover !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_clover(): string
    {
        if (!$this->has_coverage_clover()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_clover;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageCobertura
     */
    public function has_coverage_cobertura(): bool
    {
        return $this->coverage_cobertura !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_cobertura(): string
    {
        if (!$this->has_coverage_cobertura()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_cobertura;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageCrap4j
     */
    public function has_coverage_crap4j(): bool
    {
        return $this->coverage_crap4j !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_crap4j(): string
    {
        if (!$this->has_coverage_crap4j()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_crap4j;
    }
    public function coverage_crap4j_threshold(): int
    {
        return $this->coverage_crap4j_threshold;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageHtml
     */
    public function has_coverage_html(): bool
    {
        return $this->coverage_html !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_html(): string
    {
        if (!$this->has_coverage_html()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_html;
    }
    public function coverage_html_low_upper_bound(): int
    {
        return $this->coverage_html_low_upper_bound;
    }
    public function coverage_html_high_lower_bound(): int
    {
        return $this->coverage_html_high_lower_bound;
    }
    public function coverage_html_color_success_low(): string
    {
        return $this->coverage_html_color_success_low;
    }
    public function coverage_html_color_success_low_dark(): string
    {
        return $this->coverage_html_color_success_low_dark;
    }
    public function coverage_html_color_success_medium(): string
    {
        return $this->coverage_html_color_success_medium;
    }
    public function coverage_html_color_success_medium_dark(): string
    {
        return $this->coverage_html_color_success_medium_dark;
    }
    public function coverage_html_color_success_high(): string
    {
        return $this->coverage_html_color_success_high;
    }
    public function coverage_html_color_success_high_dark(): string
    {
        return $this->coverage_html_color_success_high_dark;
    }
    public function coverage_html_color_success_bar(): string
    {
        return $this->coverage_html_color_success_bar;
    }
    public function coverage_html_color_success_bar_dark(): string
    {
        return $this->coverage_html_color_success_bar_dark;
    }
    public function coverage_html_color_warning(): string
    {
        return $this->coverage_html_color_warning;
    }
    public function coverage_html_color_warning_dark(): string
    {
        return $this->coverage_html_color_warning_dark;
    }
    public function coverage_html_color_warning_bar(): string
    {
        return $this->coverage_html_color_warning_bar;
    }
    public function coverage_html_color_warning_bar_dark(): string
    {
        return $this->coverage_html_color_warning_bar_dark;
    }
    public function coverage_html_color_danger(): string
    {
        return $this->coverage_html_color_danger;
    }
    public function coverage_html_color_danger_dark(): string
    {
        return $this->coverage_html_color_danger_dark;
    }
    public function coverage_html_color_danger_bar(): string
    {
        return $this->coverage_html_color_danger_bar;
    }
    public function coverage_html_color_danger_bar_dark(): string
    {
        return $this->coverage_html_color_danger_bar_dark;
    }
    public function coverage_html_color_breadcrumbs(): string
    {
        return $this->coverage_html_color_breadcrumbs;
    }
    public function coverage_html_color_breadcrumbs_dark(): string
    {
        return $this->coverage_html_color_breadcrumbs_dark;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageHtmlCustomCssFile
     */
    public function has_coverage_html_custom_css_file(): bool
    {
        return $this->coverage_html_custom_css_file !== null;
    }
    /**
     * @throws NoCustomCssFileException
     */
    public function coverage_html_custom_css_file(): string
    {
        if (!$this->has_coverage_html_custom_css_file()) {
            throw new No_Custom_Css_File_Exception();
        }
        return $this->coverage_html_custom_css_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageOpenClover
     */
    public function has_coverage_open_clover(): bool
    {
        return $this->coverage_open_clover !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_open_clover(): string
    {
        if (!$this->has_coverage_open_clover()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_open_clover;
    }
    /**
     * @phpstan-assert-if-true !null $this->coveragePhp
     */
    public function has_coverage_php(): bool
    {
        return $this->coverage_php !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_php(): string
    {
        if (!$this->has_coverage_php()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_php;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageText
     */
    public function has_coverage_text(): bool
    {
        return $this->coverage_text !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_text(): string
    {
        if (!$this->has_coverage_text()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_text;
    }
    public function coverage_text_show_uncovered_files(): bool
    {
        return $this->coverage_text_show_uncovered_files;
    }
    public function coverage_text_show_only_summary(): bool
    {
        return $this->coverage_text_show_only_summary;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageXml
     */
    public function has_coverage_xml(): bool
    {
        return $this->coverage_xml !== null;
    }
    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverage_xml(): string
    {
        if (!$this->has_coverage_xml()) {
            throw new Code_Coverage_Report_Not_Configured_Exception();
        }
        return $this->coverage_xml;
    }
    public function coverage_xml_include_source(): bool
    {
        return $this->coverage_xml_include_source;
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
    public function do_not_fail_on_deprecation(): bool
    {
        return $this->do_not_fail_on_deprecation;
    }
    public function do_not_fail_on_phpunit_deprecation(): bool
    {
        return $this->do_not_fail_on_phpunit_deprecation;
    }
    public function do_not_fail_on_phpunit_notice(): bool
    {
        return $this->do_not_fail_on_phpunit_notice;
    }
    public function do_not_fail_on_phpunit_warning(): bool
    {
        return $this->do_not_fail_on_phpunit_warning;
    }
    public function do_not_fail_on_empty_test_suite(): bool
    {
        return $this->do_not_fail_on_empty_test_suite;
    }
    public function do_not_fail_on_incomplete(): bool
    {
        return $this->do_not_fail_on_incomplete;
    }
    public function do_not_fail_on_notice(): bool
    {
        return $this->do_not_fail_on_notice;
    }
    public function do_not_fail_on_risky(): bool
    {
        return $this->do_not_fail_on_risky;
    }
    public function do_not_fail_on_skipped(): bool
    {
        return $this->do_not_fail_on_skipped;
    }
    public function do_not_fail_on_warning(): bool
    {
        return $this->do_not_fail_on_warning;
    }
    public function stop_on_defect(): bool
    {
        return $this->stop_on_defect;
    }
    public function stop_on_deprecation(): bool
    {
        return $this->stop_on_deprecation;
    }
    /**
     * @phpstan-assert-if-true !null $this->specificDeprecationToStopOn
     */
    public function has_specific_deprecation_to_stop_on(): bool
    {
        return $this->specific_deprecation_to_stop_on !== null;
    }
    /**
     * @throws SpecificDeprecationToStopOnNotConfiguredException
     */
    public function specific_deprecation_to_stop_on(): string
    {
        if (!$this->has_specific_deprecation_to_stop_on()) {
            throw new Specific_Deprecation_To_Stop_On_Not_Configured_Exception();
        }
        return $this->specific_deprecation_to_stop_on;
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
    public function output_to_standard_error_stream(): bool
    {
        return $this->output_to_standard_error_stream;
    }
    public function columns(): int
    {
        return $this->columns;
    }
    public function no_extensions(): bool
    {
        return $this->no_extensions;
    }
    /**
     * @phpstan-assert-if-true !null $this->pharExtensionDirectory
     */
    public function has_phar_extension_directory(): bool
    {
        return $this->phar_extension_directory !== null;
    }
    /**
     * @throws NoPharExtensionDirectoryException
     *
     * @return non-empty-string
     */
    public function phar_extension_directory(): string
    {
        if (!$this->has_phar_extension_directory()) {
            throw new No_Phar_Extension_Directory_Exception();
        }
        return $this->phar_extension_directory;
    }
    /**
     * @return list<array{className: non-empty-string, parameters: array<string, string>}>
     */
    public function extension_bootstrappers(): array
    {
        return $this->extension_bootstrappers;
    }
    public function backup_globals(): bool
    {
        return $this->backup_globals;
    }
    public function backup_static_properties(): bool
    {
        return $this->backup_static_properties;
    }
    public function be_strict_about_changes_to_global_state(): bool
    {
        return $this->be_strict_about_changes_to_global_state;
    }
    public function colors(): bool
    {
        return $this->colors;
    }
    public function process_isolation(): bool
    {
        return $this->process_isolation;
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
    public function report_useless_tests(): bool
    {
        return $this->report_useless_tests;
    }
    public function strict_coverage(): bool
    {
        return $this->strict_coverage;
    }
    public function disallow_test_output(): bool
    {
        return $this->disallow_test_output;
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
    public function no_progress(): bool
    {
        return $this->no_progress;
    }
    public function no_results(): bool
    {
        return $this->no_results;
    }
    public function no_output(): bool
    {
        return $this->no_output;
    }
    public function execution_order(): int
    {
        return $this->execution_order;
    }
    public function execution_order_defects(): int
    {
        return $this->execution_order_defects;
    }
    public function resolve_dependencies(): bool
    {
        return $this->resolve_dependencies;
    }
    /**
     * @phpstan-assert-if-true !null $this->logfileTeamcity
     */
    public function has_logfile_teamcity(): bool
    {
        return $this->logfile_teamcity !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfile_teamcity(): string
    {
        if (!$this->has_logfile_teamcity()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->logfile_teamcity;
    }
    /**
     * @phpstan-assert-if-true !null $this->logfileJunit
     */
    public function has_logfile_junit(): bool
    {
        return $this->logfile_junit !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfile_junit(): string
    {
        if (!$this->has_logfile_junit()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->logfile_junit;
    }
    /**
     * @phpstan-assert-if-true !null $this->logfileOtr
     */
    public function has_logfile_otr(): bool
    {
        return $this->logfile_otr !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfile_otr(): string
    {
        if (!$this->has_logfile_otr()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->logfile_otr;
    }
    public function include_git_information_in_otr_logfile(): bool
    {
        return $this->include_git_information_in_otr_logfile;
    }
    public function include_git_information(): bool
    {
        return $this->include_git_information;
    }
    /**
     * @phpstan-assert-if-true !null $this->logfileTestdoxHtml
     */
    public function has_logfile_testdox_html(): bool
    {
        return $this->logfile_testdox_html !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfile_testdox_html(): string
    {
        if (!$this->has_logfile_testdox_html()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->logfile_testdox_html;
    }
    /**
     * @phpstan-assert-if-true !null $this->logfileTestdoxText
     */
    public function has_logfile_testdox_text(): bool
    {
        return $this->logfile_testdox_text !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfile_testdox_text(): string
    {
        if (!$this->has_logfile_testdox_text()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->logfile_testdox_text;
    }
    /**
     * @phpstan-assert-if-true !null $this->logEventsText
     */
    public function has_log_events_text(): bool
    {
        return $this->log_events_text !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function log_events_text(): string
    {
        if (!$this->has_log_events_text()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->log_events_text;
    }
    /**
     * @phpstan-assert-if-true !null $this->logEventsVerboseText
     */
    public function has_log_events_verbose_text(): bool
    {
        return $this->log_events_verbose_text !== null;
    }
    /**
     * @throws LoggingNotConfiguredException
     */
    public function log_events_verbose_text(): string
    {
        if (!$this->has_log_events_verbose_text()) {
            throw new Logging_Not_Configured_Exception();
        }
        return $this->log_events_verbose_text;
    }
    public function output_is_team_city(): bool
    {
        return $this->team_city_output;
    }
    public function output_is_test_dox(): bool
    {
        return $this->test_dox_output;
    }
    public function test_dox_output_with_summary(): bool
    {
        return $this->test_dox_output_summary;
    }
    /**
     * @phpstan-assert-if-true !empty $this->testsCovering
     */
    public function has_tests_covering(): bool
    {
        return $this->tests_covering !== null;
    }
    /**
     * @throws FilterNotConfiguredException
     *
     * @return list<string>
     */
    public function tests_covering(): array
    {
        if (!$this->has_tests_covering()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->tests_covering;
    }
    /**
     * @phpstan-assert-if-true !empty $this->testsUsing
     */
    public function has_tests_using(): bool
    {
        return $this->tests_using !== null;
    }
    /**
     * @throws FilterNotConfiguredException
     *
     * @return list<string>
     */
    public function tests_using(): array
    {
        if (!$this->has_tests_using()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->tests_using;
    }
    /**
     * @phpstan-assert-if-true !empty $this->testsRequiringPhpExtension
     */
    public function has_tests_requiring_php_extension(): bool
    {
        return $this->tests_requiring_php_extension !== null;
    }
    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-list<non-empty-string>
     */
    public function tests_requiring_php_extension(): array
    {
        if (!$this->has_tests_requiring_php_extension()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->tests_requiring_php_extension;
    }
    /**
     * @phpstan-assert-if-true !null $this->filter
     */
    public function has_filter(): bool
    {
        return $this->filter !== null;
    }
    /**
     * @throws FilterNotConfiguredException
     */
    public function filter(): string
    {
        if (!$this->has_filter()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->filter;
    }
    /**
     * @phpstan-assert-if-true !null $this->excludeFilter
     */
    public function has_exclude_filter(): bool
    {
        return $this->exclude_filter !== null;
    }
    /**
     * @throws FilterNotConfiguredException
     */
    public function exclude_filter(): string
    {
        if (!$this->has_exclude_filter()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->exclude_filter;
    }
    /**
     * @phpstan-assert-if-true !empty $this->groups
     */
    public function has_groups(): bool
    {
        return $this->groups !== [];
    }
    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-list<non-empty-string>
     */
    public function groups(): array
    {
        if (!$this->has_groups()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->groups;
    }
    /**
     * @phpstan-assert-if-true !empty $this->excludeGroups
     */
    public function has_exclude_groups(): bool
    {
        return $this->exclude_groups !== [];
    }
    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-list<non-empty-string>
     */
    public function exclude_groups(): array
    {
        if (!$this->has_exclude_groups()) {
            throw new Filter_Not_Configured_Exception();
        }
        return $this->exclude_groups;
    }
    public function random_order_seed(): int
    {
        return $this->random_order_seed;
    }
    public function include_uncovered_files(): bool
    {
        return $this->include_uncovered_files;
    }
    public function test_suite(): Test_Suite_Collection
    {
        return $this->test_suite;
    }
    /**
     * @return list<non-empty-string>
     */
    public function include_test_suites(): array
    {
        if ($this->include_test_suite === '') {
            return [];
        }
        return explode(',', $this->include_test_suite);
    }
    /**
     * @return list<non-empty-string>
     */
    public function exclude_test_suites(): array
    {
        if ($this->exclude_test_suite === '') {
            return [];
        }
        return explode(',', $this->exclude_test_suite);
    }
    /**
     * @phpstan-assert-if-true !null $this->defaultTestSuite
     */
    public function has_default_test_suite(): bool
    {
        return $this->default_test_suite !== null;
    }
    /**
     * @throws NoDefaultTestSuiteException
     */
    public function default_test_suite(): string
    {
        if (!$this->has_default_test_suite()) {
            throw new No_Default_Test_Suite_Exception();
        }
        return $this->default_test_suite;
    }
    public function ignore_test_selection_in_xml_configuration(): bool
    {
        return $this->ignore_test_selection_in_xml_configuration;
    }
    /**
     * @return non-empty-list<non-empty-string>
     */
    public function test_suffixes(): array
    {
        return $this->test_suffixes;
    }
    public function php(): Php
    {
        return $this->php;
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
     * @phpstan-assert-if-true !null $this->generateBaseline
     */
    public function has_generate_baseline(): bool
    {
        return $this->generate_baseline !== null;
    }
    /**
     * @throws NoBaselineException
     *
     * @return non-empty-string
     */
    public function generate_baseline(): string
    {
        if (!$this->has_generate_baseline()) {
            throw new No_Baseline_Exception();
        }
        return $this->generate_baseline;
    }
    public function debug(): bool
    {
        return $this->debug;
    }
    public function with_telemetry(): bool
    {
        return $this->with_telemetry;
    }
    /**
     * @return non-negative-int
     */
    public function shorten_arrays_for_export_threshold(): int
    {
        return $this->shorten_arrays_for_export_threshold;
    }
}