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
namespace Php_Unit\Text_Ui\Cli_Arguments;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Configuration
{
    /**
     * @param list<non-empty-string>                               $arguments
     * @param ?non-empty-list<non-empty-string>                    $excludeGroups
     * @param ?non-empty-list<non-empty-string>                    $groups
     * @param ?non-empty-list<non-empty-string>                    $testsCovering
     * @param ?non-empty-list<non-empty-string>                    $testsUsing
     * @param ?non-empty-list<non-empty-string>                    $testsRequiringPhpExtension
     * @param ?non-empty-array<non-empty-string, non-empty-string> $iniSettings
     * @param ?non-empty-list<non-empty-string>                    $testSuffixes
     * @param ?non-empty-list<non-empty-string>                    $coverageFilter
     * @param ?non-empty-list<non-empty-string>                    $extensions
     */
    public function __construct(private array $arguments, private ?string $test_files_file, private ?bool $all, private ?string $at_least_version, private ?bool $backup_globals, private ?bool $backup_static_properties, private ?bool $be_strict_about_changes_to_global_state, private ?string $bootstrap, private ?string $cache_directory, private ?bool $cache_result, private bool $check_php_configuration, private bool $check_version, private ?string $colors, private null|int|string $columns, private ?string $configuration_file, private ?string $coverage_clover, private ?string $coverage_cobertura, private ?string $coverage_crap4j, private ?string $coverage_html, private ?string $coverage_open_clover, private ?string $coverage_php, private ?string $coverage_text, private ?bool $coverage_text_show_uncovered_files, private ?bool $coverage_text_show_only_summary, private ?string $coverage_xml, private ?bool $exclude_source_from_xml_coverage, private ?bool $path_coverage, private bool $warm_coverage_cache, private ?int $default_time_limit, private ?bool $disable_code_coverage_ignore, private ?bool $disallow_test_output, private ?bool $enforce_time_limit, private ?array $exclude_groups, private ?int $execution_order, private ?int $execution_order_defects, private ?bool $fail_on_all_issues, private ?bool $fail_on_deprecation, private ?bool $fail_on_phpunit_deprecation, private ?bool $fail_on_phpunit_notice, private ?bool $fail_on_phpunit_warning, private ?bool $fail_on_empty_test_suite, private ?bool $fail_on_incomplete, private ?bool $fail_on_notice, private ?bool $fail_on_risky, private ?bool $fail_on_skipped, private ?bool $fail_on_warning, private ?bool $do_not_fail_on_deprecation, private ?bool $do_not_fail_on_phpunit_deprecation, private ?bool $do_not_fail_on_phpunit_notice, private ?bool $do_not_fail_on_phpunit_warning, private ?bool $do_not_fail_on_empty_test_suite, private ?bool $do_not_fail_on_incomplete, private ?bool $do_not_fail_on_notice, private ?bool $do_not_fail_on_risky, private ?bool $do_not_fail_on_skipped, private ?bool $do_not_fail_on_warning, private ?bool $stop_on_defect, private ?bool $stop_on_deprecation, private ?string $specific_deprecation_to_stop_on, private ?bool $stop_on_error, private ?bool $stop_on_failure, private ?bool $stop_on_incomplete, private ?bool $stop_on_notice, private ?bool $stop_on_risky, private ?bool $stop_on_skipped, private ?bool $stop_on_warning, private ?string $filter, private ?string $exclude_filter, private ?string $generate_baseline, private ?string $use_baseline, private bool $ignore_baseline, private bool $generate_configuration, private bool $migrate_configuration, private ?array $groups, private ?array $tests_covering, private ?array $tests_using, private ?array $tests_requiring_php_extension, private bool $help, private ?string $include_path, private ?array $ini_settings, private ?string $junit_logfile, private ?string $otr_logfile, private ?bool $include_git_information, private bool $list_groups, private bool $list_suites, private bool $list_test_files, private bool $list_tests, private ?string $list_tests_xml, private ?bool $no_coverage, private ?bool $no_extensions, private ?bool $no_output, private ?bool $no_progress, private ?bool $no_results, private ?bool $no_logging, private ?bool $process_isolation, private ?int $random_order_seed, private ?bool $report_useless_tests, private ?bool $resolve_dependencies, private ?bool $reverse_list, private ?bool $stderr, private ?bool $strict_coverage, private ?string $teamcity_logfile, private ?string $testdox_html_file, private ?string $testdox_text_file, private ?array $test_suffixes, private ?string $test_suite, private ?string $exclude_test_suite, private bool $use_default_configuration, private ?bool $display_details_on_all_issues, private ?bool $display_details_on_incomplete_tests, private ?bool $display_details_on_skipped_tests, private ?bool $display_details_on_tests_that_trigger_deprecations, private ?bool $display_details_on_phpunit_deprecations, private ?bool $display_details_on_phpunit_notices, private ?bool $display_details_on_tests_that_trigger_errors, private ?bool $display_details_on_tests_that_trigger_notices, private ?bool $display_details_on_tests_that_trigger_warnings, private bool $version, private ?array $coverage_filter, private ?string $log_events_text, private ?string $log_events_verbose_text, private ?bool $team_city_printer, private ?bool $testdox_printer, private ?bool $testdox_printer_summary, private bool $debug, private bool $with_telemetry, private ?array $extensions)
    {
    }
    /**
     * @return list<non-empty-string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }
    /**
     * @phpstan-assert-if-true !null $this->testFilesFile
     */
    public function has_test_files_file(): bool
    {
        return $this->test_files_file !== null;
    }
    /**
     * @throws Exception
     */
    public function test_files_file(): string
    {
        if (!$this->has_test_files_file()) {
            throw new Exception();
        }
        return $this->test_files_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->all
     */
    public function has_all(): bool
    {
        return $this->all !== null;
    }
    /**
     * @throws Exception
     */
    public function all(): bool
    {
        if (!$this->has_all()) {
            throw new Exception();
        }
        return $this->all;
    }
    /**
     * @phpstan-assert-if-true !null $this->atLeastVersion
     */
    public function has_at_least_version(): bool
    {
        return $this->at_least_version !== null;
    }
    /**
     * @throws Exception
     */
    public function at_least_version(): string
    {
        if (!$this->has_at_least_version()) {
            throw new Exception();
        }
        return $this->at_least_version;
    }
    /**
     * @phpstan-assert-if-true !null $this->backupGlobals
     */
    public function has_backup_globals(): bool
    {
        return $this->backup_globals !== null;
    }
    /**
     * @throws Exception
     */
    public function backup_globals(): bool
    {
        if (!$this->has_backup_globals()) {
            throw new Exception();
        }
        return $this->backup_globals;
    }
    /**
     * @phpstan-assert-if-true !null $this->backupStaticProperties
     */
    public function has_backup_static_properties(): bool
    {
        return $this->backup_static_properties !== null;
    }
    /**
     * @throws Exception
     */
    public function backup_static_properties(): bool
    {
        if (!$this->has_backup_static_properties()) {
            throw new Exception();
        }
        return $this->backup_static_properties;
    }
    /**
     * @phpstan-assert-if-true !null $this->beStrictAboutChangesToGlobalState
     */
    public function has_be_strict_about_changes_to_global_state(): bool
    {
        return $this->be_strict_about_changes_to_global_state !== null;
    }
    /**
     * @throws Exception
     */
    public function be_strict_about_changes_to_global_state(): bool
    {
        if (!$this->has_be_strict_about_changes_to_global_state()) {
            throw new Exception();
        }
        return $this->be_strict_about_changes_to_global_state;
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
            throw new Exception();
        }
        return $this->bootstrap;
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
            throw new Exception();
        }
        return $this->cache_directory;
    }
    /**
     * @phpstan-assert-if-true !null $this->cacheResult
     */
    public function has_cache_result(): bool
    {
        return $this->cache_result !== null;
    }
    /**
     * @throws Exception
     */
    public function cache_result(): bool
    {
        if (!$this->has_cache_result()) {
            throw new Exception();
        }
        return $this->cache_result;
    }
    public function check_php_configuration(): bool
    {
        return $this->check_php_configuration;
    }
    public function check_version(): bool
    {
        return $this->check_version;
    }
    /**
     * @phpstan-assert-if-true !null $this->colors
     */
    public function has_colors(): bool
    {
        return $this->colors !== null;
    }
    /**
     * @throws Exception
     */
    public function colors(): string
    {
        if (!$this->has_colors()) {
            throw new Exception();
        }
        return $this->colors;
    }
    /**
     * @phpstan-assert-if-true !null $this->columns
     */
    public function has_columns(): bool
    {
        return $this->columns !== null;
    }
    /**
     * @throws Exception
     */
    public function columns(): int|string
    {
        if (!$this->has_columns()) {
            throw new Exception();
        }
        return $this->columns;
    }
    /**
     * @phpstan-assert-if-true !null $this->configurationFile
     */
    public function has_configuration_file(): bool
    {
        return $this->configuration_file !== null;
    }
    /**
     * @throws Exception
     */
    public function configuration_file(): string
    {
        if (!$this->has_configuration_file()) {
            throw new Exception();
        }
        return $this->configuration_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageFilter
     */
    public function has_coverage_filter(): bool
    {
        return $this->coverage_filter !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function coverage_filter(): array
    {
        if (!$this->has_coverage_filter()) {
            throw new Exception();
        }
        return $this->coverage_filter;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageClover
     */
    public function has_coverage_clover(): bool
    {
        return $this->coverage_clover !== null;
    }
    /**
     * @throws Exception
     */
    public function coverage_clover(): string
    {
        if (!$this->has_coverage_clover()) {
            throw new Exception();
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
     * @throws Exception
     */
    public function coverage_cobertura(): string
    {
        if (!$this->has_coverage_cobertura()) {
            throw new Exception();
        }
        return $this->coverage_cobertura;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageCrap4J
     */
    public function has_coverage_crap4j(): bool
    {
        return $this->coverage_crap4j !== null;
    }
    /**
     * @throws Exception
     */
    public function coverage_crap4j(): string
    {
        if (!$this->has_coverage_crap4j()) {
            throw new Exception();
        }
        return $this->coverage_crap4j;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageHtml
     */
    public function has_coverage_html(): bool
    {
        return $this->coverage_html !== null;
    }
    /**
     * @throws Exception
     */
    public function coverage_html(): string
    {
        if (!$this->has_coverage_html()) {
            throw new Exception();
        }
        return $this->coverage_html;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageOpenClover
     */
    public function has_coverage_open_clover(): bool
    {
        return $this->coverage_open_clover !== null;
    }
    /**
     * @throws Exception
     */
    public function coverage_open_clover(): string
    {
        if (!$this->has_coverage_open_clover()) {
            throw new Exception();
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
     * @throws Exception
     */
    public function coverage_php(): string
    {
        if (!$this->has_coverage_php()) {
            throw new Exception();
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
     * @throws Exception
     */
    public function coverage_text(): string
    {
        if (!$this->has_coverage_text()) {
            throw new Exception();
        }
        return $this->coverage_text;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageTextShowUncoveredFiles
     */
    public function has_coverage_text_show_uncovered_files(): bool
    {
        return $this->coverage_text_show_uncovered_files !== null;
    }
    /**
     * @throws Exception
     */
    public function coverage_text_show_uncovered_files(): bool
    {
        if (!$this->has_coverage_text_show_uncovered_files()) {
            throw new Exception();
        }
        return $this->coverage_text_show_uncovered_files;
    }
    /**
     * @phpstan-assert-if-true !null $this->coverageTextShowOnlySummary
     */
    public function has_coverage_text_show_only_summary(): bool
    {
        return $this->coverage_text_show_only_summary !== null;
    }
    /**
     * @throws Exception
     */
    public function coverage_text_show_only_summary(): bool
    {
        if (!$this->has_coverage_text_show_only_summary()) {
            throw new Exception();
        }
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
     * @throws Exception
     */
    public function coverage_xml(): string
    {
        if (!$this->has_coverage_xml()) {
            throw new Exception();
        }
        return $this->coverage_xml;
    }
    /**
     * @phpstan-assert-if-true !null $this->excludeSourceFromXmlCoverage
     */
    public function has_exclude_source_from_xml_coverage(): bool
    {
        return $this->exclude_source_from_xml_coverage !== null;
    }
    /**
     * @throws Exception
     */
    public function exclude_source_from_xml_coverage(): bool
    {
        if (!$this->has_exclude_source_from_xml_coverage()) {
            throw new Exception();
        }
        return $this->exclude_source_from_xml_coverage;
    }
    /**
     * @phpstan-assert-if-true !null $this->pathCoverage
     */
    public function has_path_coverage(): bool
    {
        return $this->path_coverage !== null;
    }
    /**
     * @throws Exception
     */
    public function path_coverage(): bool
    {
        if (!$this->has_path_coverage()) {
            throw new Exception();
        }
        return $this->path_coverage;
    }
    public function warm_coverage_cache(): bool
    {
        return $this->warm_coverage_cache;
    }
    /**
     * @phpstan-assert-if-true !null $this->defaultTimeLimit
     */
    public function has_default_time_limit(): bool
    {
        return $this->default_time_limit !== null;
    }
    /**
     * @throws Exception
     */
    public function default_time_limit(): int
    {
        if (!$this->has_default_time_limit()) {
            throw new Exception();
        }
        return $this->default_time_limit;
    }
    /**
     * @phpstan-assert-if-true !null $this->disableCodeCoverageIgnore
     */
    public function has_disable_code_coverage_ignore(): bool
    {
        return $this->disable_code_coverage_ignore !== null;
    }
    /**
     * @throws Exception
     */
    public function disable_code_coverage_ignore(): bool
    {
        if (!$this->has_disable_code_coverage_ignore()) {
            throw new Exception();
        }
        return $this->disable_code_coverage_ignore;
    }
    /**
     * @phpstan-assert-if-true !null $this->disallowTestOutput
     */
    public function has_disallow_test_output(): bool
    {
        return $this->disallow_test_output !== null;
    }
    /**
     * @throws Exception
     */
    public function disallow_test_output(): bool
    {
        if (!$this->has_disallow_test_output()) {
            throw new Exception();
        }
        return $this->disallow_test_output;
    }
    /**
     * @phpstan-assert-if-true !null $this->enforceTimeLimit
     */
    public function has_enforce_time_limit(): bool
    {
        return $this->enforce_time_limit !== null;
    }
    /**
     * @throws Exception
     */
    public function enforce_time_limit(): bool
    {
        if (!$this->has_enforce_time_limit()) {
            throw new Exception();
        }
        return $this->enforce_time_limit;
    }
    /**
     * @phpstan-assert-if-true !null $this->excludeGroups
     */
    public function has_exclude_groups(): bool
    {
        return $this->exclude_groups !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function exclude_groups(): array
    {
        if (!$this->has_exclude_groups()) {
            throw new Exception();
        }
        return $this->exclude_groups;
    }
    /**
     * @phpstan-assert-if-true !null $this->executionOrder
     */
    public function has_execution_order(): bool
    {
        return $this->execution_order !== null;
    }
    /**
     * @throws Exception
     */
    public function execution_order(): int
    {
        if (!$this->has_execution_order()) {
            throw new Exception();
        }
        return $this->execution_order;
    }
    /**
     * @phpstan-assert-if-true !null $this->executionOrderDefects
     */
    public function has_execution_order_defects(): bool
    {
        return $this->execution_order_defects !== null;
    }
    /**
     * @throws Exception
     */
    public function execution_order_defects(): int
    {
        if (!$this->has_execution_order_defects()) {
            throw new Exception();
        }
        return $this->execution_order_defects;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnAllIssues
     */
    public function has_fail_on_all_issues(): bool
    {
        return $this->fail_on_all_issues !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_all_issues(): bool
    {
        if (!$this->has_fail_on_all_issues()) {
            throw new Exception();
        }
        return $this->fail_on_all_issues;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnDeprecation
     */
    public function has_fail_on_deprecation(): bool
    {
        return $this->fail_on_deprecation !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_deprecation(): bool
    {
        if (!$this->has_fail_on_deprecation()) {
            throw new Exception();
        }
        return $this->fail_on_deprecation;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnPhpunitDeprecation
     */
    public function has_fail_on_phpunit_deprecation(): bool
    {
        return $this->fail_on_phpunit_deprecation !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_phpunit_deprecation(): bool
    {
        if (!$this->has_fail_on_phpunit_deprecation()) {
            throw new Exception();
        }
        return $this->fail_on_phpunit_deprecation;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnPhpunitNotice
     */
    public function has_fail_on_phpunit_notice(): bool
    {
        return $this->fail_on_phpunit_notice !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_phpunit_notice(): bool
    {
        if (!$this->has_fail_on_phpunit_notice()) {
            throw new Exception();
        }
        return $this->fail_on_phpunit_notice;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnPhpunitWarning
     */
    public function has_fail_on_phpunit_warning(): bool
    {
        return $this->fail_on_phpunit_warning !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_phpunit_warning(): bool
    {
        if (!$this->has_fail_on_phpunit_warning()) {
            throw new Exception();
        }
        return $this->fail_on_phpunit_warning;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnEmptyTestSuite
     */
    public function has_fail_on_empty_test_suite(): bool
    {
        return $this->fail_on_empty_test_suite !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_empty_test_suite(): bool
    {
        if (!$this->has_fail_on_empty_test_suite()) {
            throw new Exception();
        }
        return $this->fail_on_empty_test_suite;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnIncomplete
     */
    public function has_fail_on_incomplete(): bool
    {
        return $this->fail_on_incomplete !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_incomplete(): bool
    {
        if (!$this->has_fail_on_incomplete()) {
            throw new Exception();
        }
        return $this->fail_on_incomplete;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnNotice
     */
    public function has_fail_on_notice(): bool
    {
        return $this->fail_on_notice !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_notice(): bool
    {
        if (!$this->has_fail_on_notice()) {
            throw new Exception();
        }
        return $this->fail_on_notice;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnRisky
     */
    public function has_fail_on_risky(): bool
    {
        return $this->fail_on_risky !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_risky(): bool
    {
        if (!$this->has_fail_on_risky()) {
            throw new Exception();
        }
        return $this->fail_on_risky;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnSkipped
     */
    public function has_fail_on_skipped(): bool
    {
        return $this->fail_on_skipped !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_skipped(): bool
    {
        if (!$this->has_fail_on_skipped()) {
            throw new Exception();
        }
        return $this->fail_on_skipped;
    }
    /**
     * @phpstan-assert-if-true !null $this->failOnWarning
     */
    public function has_fail_on_warning(): bool
    {
        return $this->fail_on_warning !== null;
    }
    /**
     * @throws Exception
     */
    public function fail_on_warning(): bool
    {
        if (!$this->has_fail_on_warning()) {
            throw new Exception();
        }
        return $this->fail_on_warning;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnDeprecation
     */
    public function has_do_not_fail_on_deprecation(): bool
    {
        return $this->do_not_fail_on_deprecation !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_deprecation(): bool
    {
        if (!$this->has_do_not_fail_on_deprecation()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_deprecation;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnPhpunitDeprecation
     */
    public function has_do_not_fail_on_phpunit_deprecation(): bool
    {
        return $this->do_not_fail_on_phpunit_deprecation !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_phpunit_deprecation(): bool
    {
        if (!$this->has_do_not_fail_on_phpunit_deprecation()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_phpunit_deprecation;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnPhpunitNotice
     */
    public function has_do_not_fail_on_phpunit_notice(): bool
    {
        return $this->do_not_fail_on_phpunit_notice !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_phpunit_notice(): bool
    {
        if (!$this->has_do_not_fail_on_phpunit_notice()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_phpunit_notice;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnPhpunitWarning
     */
    public function has_do_not_fail_on_phpunit_warning(): bool
    {
        return $this->do_not_fail_on_phpunit_warning !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_phpunit_warning(): bool
    {
        if (!$this->has_do_not_fail_on_phpunit_warning()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_phpunit_warning;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnEmptyTestSuite
     */
    public function has_do_not_fail_on_empty_test_suite(): bool
    {
        return $this->do_not_fail_on_empty_test_suite !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_empty_test_suite(): bool
    {
        if (!$this->has_do_not_fail_on_empty_test_suite()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_empty_test_suite;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnIncomplete
     */
    public function has_do_not_fail_on_incomplete(): bool
    {
        return $this->do_not_fail_on_incomplete !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_incomplete(): bool
    {
        if (!$this->has_do_not_fail_on_incomplete()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_incomplete;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnNotice
     */
    public function has_do_not_fail_on_notice(): bool
    {
        return $this->do_not_fail_on_notice !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_notice(): bool
    {
        if (!$this->has_do_not_fail_on_notice()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_notice;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnRisky
     */
    public function has_do_not_fail_on_risky(): bool
    {
        return $this->do_not_fail_on_risky !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_risky(): bool
    {
        if (!$this->has_do_not_fail_on_risky()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_risky;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnSkipped
     */
    public function has_do_not_fail_on_skipped(): bool
    {
        return $this->do_not_fail_on_skipped !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_skipped(): bool
    {
        if (!$this->has_do_not_fail_on_skipped()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_skipped;
    }
    /**
     * @phpstan-assert-if-true !null $this->doNotFailOnWarning
     */
    public function has_do_not_fail_on_warning(): bool
    {
        return $this->do_not_fail_on_warning !== null;
    }
    /**
     * @throws Exception
     */
    public function do_not_fail_on_warning(): bool
    {
        if (!$this->has_do_not_fail_on_warning()) {
            throw new Exception();
        }
        return $this->do_not_fail_on_warning;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnDefect
     */
    public function has_stop_on_defect(): bool
    {
        return $this->stop_on_defect !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_defect(): bool
    {
        if (!$this->has_stop_on_defect()) {
            throw new Exception();
        }
        return $this->stop_on_defect;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnDeprecation
     */
    public function has_stop_on_deprecation(): bool
    {
        return $this->stop_on_deprecation !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_deprecation(): bool
    {
        if (!$this->has_stop_on_deprecation()) {
            throw new Exception();
        }
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
     * @throws Exception
     */
    public function specific_deprecation_to_stop_on(): string
    {
        if (!$this->has_specific_deprecation_to_stop_on()) {
            throw new Exception();
        }
        return $this->specific_deprecation_to_stop_on;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnError
     */
    public function has_stop_on_error(): bool
    {
        return $this->stop_on_error !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_error(): bool
    {
        if (!$this->has_stop_on_error()) {
            throw new Exception();
        }
        return $this->stop_on_error;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnFailure
     */
    public function has_stop_on_failure(): bool
    {
        return $this->stop_on_failure !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_failure(): bool
    {
        if (!$this->has_stop_on_failure()) {
            throw new Exception();
        }
        return $this->stop_on_failure;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnIncomplete
     */
    public function has_stop_on_incomplete(): bool
    {
        return $this->stop_on_incomplete !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_incomplete(): bool
    {
        if (!$this->has_stop_on_incomplete()) {
            throw new Exception();
        }
        return $this->stop_on_incomplete;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnNotice
     */
    public function has_stop_on_notice(): bool
    {
        return $this->stop_on_notice !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_notice(): bool
    {
        if (!$this->has_stop_on_notice()) {
            throw new Exception();
        }
        return $this->stop_on_notice;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnRisky
     */
    public function has_stop_on_risky(): bool
    {
        return $this->stop_on_risky !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_risky(): bool
    {
        if (!$this->has_stop_on_risky()) {
            throw new Exception();
        }
        return $this->stop_on_risky;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnSkipped
     */
    public function has_stop_on_skipped(): bool
    {
        return $this->stop_on_skipped !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_skipped(): bool
    {
        if (!$this->has_stop_on_skipped()) {
            throw new Exception();
        }
        return $this->stop_on_skipped;
    }
    /**
     * @phpstan-assert-if-true !null $this->stopOnWarning
     */
    public function has_stop_on_warning(): bool
    {
        return $this->stop_on_warning !== null;
    }
    /**
     * @throws Exception
     */
    public function stop_on_warning(): bool
    {
        if (!$this->has_stop_on_warning()) {
            throw new Exception();
        }
        return $this->stop_on_warning;
    }
    /**
     * @phpstan-assert-if-true !null $this->excludeFilter
     */
    public function has_exclude_filter(): bool
    {
        return $this->exclude_filter !== null;
    }
    /**
     * @throws Exception
     */
    public function exclude_filter(): string
    {
        if (!$this->has_exclude_filter()) {
            throw new Exception();
        }
        return $this->exclude_filter;
    }
    /**
     * @phpstan-assert-if-true !null $this->filter
     */
    public function has_filter(): bool
    {
        return $this->filter !== null;
    }
    /**
     * @throws Exception
     */
    public function filter(): string
    {
        if (!$this->has_filter()) {
            throw new Exception();
        }
        return $this->filter;
    }
    /**
     * @phpstan-assert-if-true !null $this->generateBaseline
     */
    public function has_generate_baseline(): bool
    {
        return $this->generate_baseline !== null;
    }
    /**
     * @throws Exception
     */
    public function generate_baseline(): string
    {
        if (!$this->has_generate_baseline()) {
            throw new Exception();
        }
        return $this->generate_baseline;
    }
    /**
     * @phpstan-assert-if-true !null $this->useBaseline
     */
    public function has_use_baseline(): bool
    {
        return $this->use_baseline !== null;
    }
    /**
     * @throws Exception
     */
    public function use_baseline(): string
    {
        if (!$this->has_use_baseline()) {
            throw new Exception();
        }
        return $this->use_baseline;
    }
    public function ignore_baseline(): bool
    {
        return $this->ignore_baseline;
    }
    public function generate_configuration(): bool
    {
        return $this->generate_configuration;
    }
    public function migrate_configuration(): bool
    {
        return $this->migrate_configuration;
    }
    /**
     * @phpstan-assert-if-true !null $this->groups
     */
    public function has_groups(): bool
    {
        return $this->groups !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function groups(): array
    {
        if (!$this->has_groups()) {
            throw new Exception();
        }
        return $this->groups;
    }
    /**
     * @phpstan-assert-if-true !null $this->testsCovering
     */
    public function has_tests_covering(): bool
    {
        return $this->tests_covering !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function tests_covering(): array
    {
        if (!$this->has_tests_covering()) {
            throw new Exception();
        }
        return $this->tests_covering;
    }
    /**
     * @phpstan-assert-if-true !null $this->testsUsing
     */
    public function has_tests_using(): bool
    {
        return $this->tests_using !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function tests_using(): array
    {
        if (!$this->has_tests_using()) {
            throw new Exception();
        }
        return $this->tests_using;
    }
    /**
     * @phpstan-assert-if-true !null $this->testsRequiringPhpExtension
     */
    public function has_tests_requiring_php_extension(): bool
    {
        return $this->tests_requiring_php_extension !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function tests_requiring_php_extension(): array
    {
        if (!$this->has_tests_requiring_php_extension()) {
            throw new Exception();
        }
        return $this->tests_requiring_php_extension;
    }
    public function help(): bool
    {
        return $this->help;
    }
    /**
     * @phpstan-assert-if-true !null $this->includePath
     */
    public function has_include_path(): bool
    {
        return $this->include_path !== null;
    }
    /**
     * @throws Exception
     */
    public function include_path(): string
    {
        if (!$this->has_include_path()) {
            throw new Exception();
        }
        return $this->include_path;
    }
    /**
     * @phpstan-assert-if-true !null $this->iniSettings
     */
    public function has_ini_settings(): bool
    {
        return $this->ini_settings !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-array<non-empty-string, non-empty-string>
     */
    public function ini_settings(): array
    {
        if (!$this->has_ini_settings()) {
            throw new Exception();
        }
        return $this->ini_settings;
    }
    /**
     * @phpstan-assert-if-true !null $this->junitLogfile
     */
    public function has_junit_logfile(): bool
    {
        return $this->junit_logfile !== null;
    }
    /**
     * @throws Exception
     */
    public function junit_logfile(): string
    {
        if (!$this->has_junit_logfile()) {
            throw new Exception();
        }
        return $this->junit_logfile;
    }
    /**
     * @phpstan-assert-if-true !null $this->otrLogfile
     */
    public function has_otr_logfile(): bool
    {
        return $this->otr_logfile !== null;
    }
    /**
     * @throws Exception
     */
    public function otr_logfile(): string
    {
        if (!$this->has_otr_logfile()) {
            throw new Exception();
        }
        return $this->otr_logfile;
    }
    /**
     * @phpstan-assert-if-true !null $this->includeGitInformation
     */
    public function has_include_git_information(): bool
    {
        return $this->include_git_information !== null;
    }
    /**
     * @throws Exception
     */
    public function include_git_information(): bool
    {
        if (!$this->has_include_git_information()) {
            throw new Exception();
        }
        return $this->include_git_information;
    }
    public function list_groups(): bool
    {
        return $this->list_groups;
    }
    public function list_suites(): bool
    {
        return $this->list_suites;
    }
    public function list_test_files(): bool
    {
        return $this->list_test_files;
    }
    public function list_tests(): bool
    {
        return $this->list_tests;
    }
    /**
     * @phpstan-assert-if-true !null $this->listTestsXml
     */
    public function has_list_tests_xml(): bool
    {
        return $this->list_tests_xml !== null;
    }
    /**
     * @throws Exception
     */
    public function list_tests_xml(): string
    {
        if (!$this->has_list_tests_xml()) {
            throw new Exception();
        }
        return $this->list_tests_xml;
    }
    /**
     * @phpstan-assert-if-true !null $this->noCoverage
     */
    public function has_no_coverage(): bool
    {
        return $this->no_coverage !== null;
    }
    /**
     * @throws Exception
     */
    public function no_coverage(): bool
    {
        if (!$this->has_no_coverage()) {
            throw new Exception();
        }
        return $this->no_coverage;
    }
    /**
     * @phpstan-assert-if-true !null $this->noExtensions
     */
    public function has_no_extensions(): bool
    {
        return $this->no_extensions !== null;
    }
    /**
     * @throws Exception
     */
    public function no_extensions(): bool
    {
        if (!$this->has_no_extensions()) {
            throw new Exception();
        }
        return $this->no_extensions;
    }
    /**
     * @phpstan-assert-if-true !null $this->noOutput
     */
    public function has_no_output(): bool
    {
        return $this->no_output !== null;
    }
    /**
     * @throws Exception
     */
    public function no_output(): bool
    {
        if ($this->no_output === null) {
            throw new Exception();
        }
        return $this->no_output;
    }
    /**
     * @phpstan-assert-if-true !null $this->noProgress
     */
    public function has_no_progress(): bool
    {
        return $this->no_progress !== null;
    }
    /**
     * @throws Exception
     */
    public function no_progress(): bool
    {
        if ($this->no_progress === null) {
            throw new Exception();
        }
        return $this->no_progress;
    }
    /**
     * @phpstan-assert-if-true !null $this->noResults
     */
    public function has_no_results(): bool
    {
        return $this->no_results !== null;
    }
    /**
     * @throws Exception
     */
    public function no_results(): bool
    {
        if ($this->no_results === null) {
            throw new Exception();
        }
        return $this->no_results;
    }
    /**
     * @phpstan-assert-if-true !null $this->noLogging
     */
    public function has_no_logging(): bool
    {
        return $this->no_logging !== null;
    }
    /**
     * @throws Exception
     */
    public function no_logging(): bool
    {
        if (!$this->has_no_logging()) {
            throw new Exception();
        }
        return $this->no_logging;
    }
    /**
     * @phpstan-assert-if-true !null $this->processIsolation
     */
    public function has_process_isolation(): bool
    {
        return $this->process_isolation !== null;
    }
    /**
     * @throws Exception
     */
    public function process_isolation(): bool
    {
        if (!$this->has_process_isolation()) {
            throw new Exception();
        }
        return $this->process_isolation;
    }
    /**
     * @phpstan-assert-if-true !null $this->randomOrderSeed
     */
    public function has_random_order_seed(): bool
    {
        return $this->random_order_seed !== null;
    }
    /**
     * @throws Exception
     */
    public function random_order_seed(): int
    {
        if (!$this->has_random_order_seed()) {
            throw new Exception();
        }
        return $this->random_order_seed;
    }
    /**
     * @phpstan-assert-if-true !null $this->reportUselessTests
     */
    public function has_report_useless_tests(): bool
    {
        return $this->report_useless_tests !== null;
    }
    /**
     * @throws Exception
     */
    public function report_useless_tests(): bool
    {
        if (!$this->has_report_useless_tests()) {
            throw new Exception();
        }
        return $this->report_useless_tests;
    }
    /**
     * @phpstan-assert-if-true !null $this->resolveDependencies
     */
    public function has_resolve_dependencies(): bool
    {
        return $this->resolve_dependencies !== null;
    }
    /**
     * @throws Exception
     */
    public function resolve_dependencies(): bool
    {
        if (!$this->has_resolve_dependencies()) {
            throw new Exception();
        }
        return $this->resolve_dependencies;
    }
    /**
     * @phpstan-assert-if-true !null $this->reverseList
     */
    public function has_reverse_list(): bool
    {
        return $this->reverse_list !== null;
    }
    /**
     * @throws Exception
     */
    public function reverse_list(): bool
    {
        if (!$this->has_reverse_list()) {
            throw new Exception();
        }
        return $this->reverse_list;
    }
    /**
     * @phpstan-assert-if-true !null $this->stderr
     */
    public function has_stderr(): bool
    {
        return $this->stderr !== null;
    }
    /**
     * @throws Exception
     */
    public function stderr(): bool
    {
        if (!$this->has_stderr()) {
            throw new Exception();
        }
        return $this->stderr;
    }
    /**
     * @phpstan-assert-if-true !null $this->strictCoverage
     */
    public function has_strict_coverage(): bool
    {
        return $this->strict_coverage !== null;
    }
    /**
     * @throws Exception
     */
    public function strict_coverage(): bool
    {
        if (!$this->has_strict_coverage()) {
            throw new Exception();
        }
        return $this->strict_coverage;
    }
    /**
     * @phpstan-assert-if-true !null $this->teamcityLogfile
     */
    public function has_teamcity_logfile(): bool
    {
        return $this->teamcity_logfile !== null;
    }
    /**
     * @throws Exception
     */
    public function teamcity_logfile(): string
    {
        if (!$this->has_teamcity_logfile()) {
            throw new Exception();
        }
        return $this->teamcity_logfile;
    }
    /**
     * @phpstan-assert-if-true !null $this->teamCityPrinter
     */
    public function has_team_city_printer(): bool
    {
        return $this->team_city_printer !== null;
    }
    /**
     * @throws Exception
     */
    public function team_city_printer(): bool
    {
        if (!$this->has_team_city_printer()) {
            throw new Exception();
        }
        return $this->team_city_printer;
    }
    /**
     * @phpstan-assert-if-true !null $this->testdoxHtmlFile
     */
    public function has_testdox_html_file(): bool
    {
        return $this->testdox_html_file !== null;
    }
    /**
     * @throws Exception
     */
    public function testdox_html_file(): string
    {
        if (!$this->has_testdox_html_file()) {
            throw new Exception();
        }
        return $this->testdox_html_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->testdoxTextFile
     */
    public function has_testdox_text_file(): bool
    {
        return $this->testdox_text_file !== null;
    }
    /**
     * @throws Exception
     */
    public function testdox_text_file(): string
    {
        if (!$this->has_testdox_text_file()) {
            throw new Exception();
        }
        return $this->testdox_text_file;
    }
    /**
     * @phpstan-assert-if-true !null $this->testdoxPrinter
     */
    public function has_test_dox_printer(): bool
    {
        return $this->testdox_printer !== null;
    }
    /**
     * @throws Exception
     */
    public function testdox_printer(): bool
    {
        if (!$this->has_test_dox_printer()) {
            throw new Exception();
        }
        return $this->testdox_printer;
    }
    /**
     * @phpstan-assert-if-true !null $this->testdoxPrinterSummary
     */
    public function has_test_dox_printer_summary(): bool
    {
        return $this->testdox_printer_summary !== null;
    }
    /**
     * @throws Exception
     */
    public function testdox_printer_summary(): bool
    {
        if (!$this->has_test_dox_printer_summary()) {
            throw new Exception();
        }
        return $this->testdox_printer_summary;
    }
    /**
     * @phpstan-assert-if-true !null $this->testSuffixes
     */
    public function has_test_suffixes(): bool
    {
        return $this->test_suffixes !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function test_suffixes(): array
    {
        if (!$this->has_test_suffixes()) {
            throw new Exception();
        }
        return $this->test_suffixes;
    }
    /**
     * @phpstan-assert-if-true !null $this->testSuite
     */
    public function has_test_suite(): bool
    {
        return $this->test_suite !== null;
    }
    /**
     * @throws Exception
     */
    public function test_suite(): string
    {
        if (!$this->has_test_suite()) {
            throw new Exception();
        }
        return $this->test_suite;
    }
    /**
     * @phpstan-assert-if-true !null $this->excludeTestSuite
     */
    public function has_excluded_test_suite(): bool
    {
        return $this->exclude_test_suite !== null;
    }
    /**
     * @throws Exception
     */
    public function excluded_test_suite(): string
    {
        if (!$this->has_excluded_test_suite()) {
            throw new Exception();
        }
        return $this->exclude_test_suite;
    }
    public function use_default_configuration(): bool
    {
        return $this->use_default_configuration;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnAllIssues
     */
    public function has_display_details_on_all_issues(): bool
    {
        return $this->display_details_on_all_issues !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_all_issues(): bool
    {
        if (!$this->has_display_details_on_all_issues()) {
            throw new Exception();
        }
        return $this->display_details_on_all_issues;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnIncompleteTests
     */
    public function has_display_details_on_incomplete_tests(): bool
    {
        return $this->display_details_on_incomplete_tests !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_incomplete_tests(): bool
    {
        if (!$this->has_display_details_on_incomplete_tests()) {
            throw new Exception();
        }
        return $this->display_details_on_incomplete_tests;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnSkippedTests
     */
    public function has_display_details_on_skipped_tests(): bool
    {
        return $this->display_details_on_skipped_tests !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_skipped_tests(): bool
    {
        if (!$this->has_display_details_on_skipped_tests()) {
            throw new Exception();
        }
        return $this->display_details_on_skipped_tests;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnTestsThatTriggerDeprecations
     */
    public function has_display_details_on_tests_that_trigger_deprecations(): bool
    {
        return $this->display_details_on_tests_that_trigger_deprecations !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_tests_that_trigger_deprecations(): bool
    {
        if (!$this->has_display_details_on_tests_that_trigger_deprecations()) {
            throw new Exception();
        }
        return $this->display_details_on_tests_that_trigger_deprecations;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnPhpunitDeprecations
     */
    public function has_display_details_on_phpunit_deprecations(): bool
    {
        return $this->display_details_on_phpunit_deprecations !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_phpunit_deprecations(): bool
    {
        if (!$this->has_display_details_on_phpunit_deprecations()) {
            throw new Exception();
        }
        return $this->display_details_on_phpunit_deprecations;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnPhpunitNotices
     */
    public function has_display_details_on_phpunit_notices(): bool
    {
        return $this->display_details_on_phpunit_notices !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_phpunit_notices(): bool
    {
        if (!$this->has_display_details_on_phpunit_notices()) {
            throw new Exception();
        }
        return $this->display_details_on_phpunit_notices;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnTestsThatTriggerErrors
     */
    public function has_display_details_on_tests_that_trigger_errors(): bool
    {
        return $this->display_details_on_tests_that_trigger_errors !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_tests_that_trigger_errors(): bool
    {
        if (!$this->has_display_details_on_tests_that_trigger_errors()) {
            throw new Exception();
        }
        return $this->display_details_on_tests_that_trigger_errors;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnTestsThatTriggerNotices
     */
    public function has_display_details_on_tests_that_trigger_notices(): bool
    {
        return $this->display_details_on_tests_that_trigger_notices !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_tests_that_trigger_notices(): bool
    {
        if (!$this->has_display_details_on_tests_that_trigger_notices()) {
            throw new Exception();
        }
        return $this->display_details_on_tests_that_trigger_notices;
    }
    /**
     * @phpstan-assert-if-true !null $this->displayDetailsOnTestsThatTriggerWarnings
     */
    public function has_display_details_on_tests_that_trigger_warnings(): bool
    {
        return $this->display_details_on_tests_that_trigger_warnings !== null;
    }
    /**
     * @throws Exception
     */
    public function display_details_on_tests_that_trigger_warnings(): bool
    {
        if (!$this->has_display_details_on_tests_that_trigger_warnings()) {
            throw new Exception();
        }
        return $this->display_details_on_tests_that_trigger_warnings;
    }
    public function version(): bool
    {
        return $this->version;
    }
    /**
     * @phpstan-assert-if-true !null $this->logEventsText
     */
    public function has_log_events_text(): bool
    {
        return $this->log_events_text !== null;
    }
    /**
     * @throws Exception
     */
    public function log_events_text(): string
    {
        if (!$this->has_log_events_text()) {
            throw new Exception();
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
     * @throws Exception
     */
    public function log_events_verbose_text(): string
    {
        if (!$this->has_log_events_verbose_text()) {
            throw new Exception();
        }
        return $this->log_events_verbose_text;
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
     * @phpstan-assert-if-true !null $this->extensions
     */
    public function has_extensions(): bool
    {
        return $this->extensions !== null;
    }
    /**
     * @throws Exception
     *
     * @return non-empty-list<non-empty-string>
     */
    public function extensions(): array
    {
        if (!$this->has_extensions()) {
            throw new Exception();
        }
        return $this->extensions;
    }
}