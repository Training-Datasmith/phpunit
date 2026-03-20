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

use function array_diff;
use function assert;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function explode;
use function is_int;
use const PATH_SEPARATOR;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Runner\Test_Suite_Sorter;
use Php_Unit\Text_Ui\Cli_Arguments\Configuration as CliConfiguration;
use Php_Unit\Text_Ui\Cli_Arguments\Exception;
use Php_Unit\Text_Ui\Xml_Configuration\Configuration as XmlConfiguration;
use Php_Unit\Text_Ui\Xml_Configuration\Loaded_From_File_Configuration;
use Php_Unit\Text_Ui\Xml_Configuration\Schema_Detector;
use Php_Unit\Util\Filesystem;
use function realpath;
use Sebastian_Bergmann\Code_Coverage\Report\Html\Colors;
use Sebastian_Bergmann\Code_Coverage\Report\Thresholds;
use Sebastian_Bergmann\Environment\Console;
use Sebastian_Bergmann\Invoker\Invoker;
use function time;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Merger
{
    /**
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     * @throws Exception
     * @throws NoCustomCssFileException
     */
    public function merge(Cli_Configuration $cli_configuration, Xml_Configuration $xml_configuration): Configuration
    {
        $test_files_file = null;
        if ($cli_configuration->has_test_files_file()) {
            $test_files_file = $cli_configuration->test_files_file();
        }
        $configuration_file = null;
        if ($xml_configuration->was_loaded_from_file()) {
            assert($xml_configuration instanceof Loaded_From_File_Configuration);
            $configuration_file = $xml_configuration->filename();
        }
        $bootstrap = null;
        if ($cli_configuration->has_bootstrap()) {
            $bootstrap = $cli_configuration->bootstrap();
        } elseif ($xml_configuration->phpunit()->has_bootstrap()) {
            $bootstrap = $xml_configuration->phpunit()->bootstrap();
        }
        if ($cli_configuration->has_cache_result()) {
            $cache_result = $cli_configuration->cache_result();
        } else {
            $cache_result = $xml_configuration->phpunit()->cache_result();
        }
        $cache_directory = null;
        $coverage_cache_directory = null;
        if ($cli_configuration->has_cache_directory() && Filesystem::create_directory($cli_configuration->cache_directory())) {
            $cache_directory = realpath($cli_configuration->cache_directory());
        } elseif ($xml_configuration->phpunit()->has_cache_directory() && Filesystem::create_directory($xml_configuration->phpunit()->cache_directory())) {
            $cache_directory = realpath($xml_configuration->phpunit()->cache_directory());
        }
        if ($cache_directory !== null) {
            $coverage_cache_directory = $cache_directory . DIRECTORY_SEPARATOR . 'code-coverage';
            $test_result_cache_file = $cache_directory . DIRECTORY_SEPARATOR . 'test-results';
        }
        if (!isset($test_result_cache_file)) {
            if ($xml_configuration->was_loaded_from_file()) {
                $test_result_cache_file = dirname(realpath($xml_configuration->filename())) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
            } else {
                $candidate = realpath($_SERVER['PHP_SELF']);
                if ($candidate) {
                    $test_result_cache_file = dirname($candidate) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
                } else {
                    $test_result_cache_file = '.phpunit.result.cache';
                }
            }
        }
        if ($cli_configuration->has_disable_code_coverage_ignore()) {
            $disable_code_coverage_ignore = $cli_configuration->disable_code_coverage_ignore();
        } else {
            $disable_code_coverage_ignore = $xml_configuration->code_coverage()->disable_code_coverage_ignore();
        }
        if ($cli_configuration->has_fail_on_all_issues()) {
            $fail_on_all_issues = $cli_configuration->fail_on_all_issues();
        } else {
            $fail_on_all_issues = $xml_configuration->phpunit()->fail_on_all_issues();
        }
        if ($cli_configuration->has_fail_on_deprecation()) {
            $fail_on_deprecation = $cli_configuration->fail_on_deprecation();
        } else {
            $fail_on_deprecation = $xml_configuration->phpunit()->fail_on_deprecation();
        }
        if ($cli_configuration->has_fail_on_phpunit_deprecation()) {
            $fail_on_phpunit_deprecation = $cli_configuration->fail_on_phpunit_deprecation();
        } else {
            $fail_on_phpunit_deprecation = $xml_configuration->phpunit()->fail_on_phpunit_deprecation();
        }
        if ($cli_configuration->has_fail_on_phpunit_notice()) {
            $fail_on_phpunit_notice = $cli_configuration->fail_on_phpunit_notice();
        } else {
            $fail_on_phpunit_notice = $xml_configuration->phpunit()->fail_on_phpunit_notice();
        }
        if ($cli_configuration->has_fail_on_phpunit_warning()) {
            $fail_on_phpunit_warning = $cli_configuration->fail_on_phpunit_warning();
        } else {
            $fail_on_phpunit_warning = $xml_configuration->phpunit()->fail_on_phpunit_warning();
        }
        if ($cli_configuration->has_fail_on_empty_test_suite()) {
            $fail_on_empty_test_suite = $cli_configuration->fail_on_empty_test_suite();
        } else {
            $fail_on_empty_test_suite = $xml_configuration->phpunit()->fail_on_empty_test_suite();
        }
        if ($cli_configuration->has_fail_on_incomplete()) {
            $fail_on_incomplete = $cli_configuration->fail_on_incomplete();
        } else {
            $fail_on_incomplete = $xml_configuration->phpunit()->fail_on_incomplete();
        }
        if ($cli_configuration->has_fail_on_notice()) {
            $fail_on_notice = $cli_configuration->fail_on_notice();
        } else {
            $fail_on_notice = $xml_configuration->phpunit()->fail_on_notice();
        }
        if ($cli_configuration->has_fail_on_risky()) {
            $fail_on_risky = $cli_configuration->fail_on_risky();
        } else {
            $fail_on_risky = $xml_configuration->phpunit()->fail_on_risky();
        }
        if ($cli_configuration->has_fail_on_skipped()) {
            $fail_on_skipped = $cli_configuration->fail_on_skipped();
        } else {
            $fail_on_skipped = $xml_configuration->phpunit()->fail_on_skipped();
        }
        if ($cli_configuration->has_fail_on_warning()) {
            $fail_on_warning = $cli_configuration->fail_on_warning();
        } else {
            $fail_on_warning = $xml_configuration->phpunit()->fail_on_warning();
        }
        $do_not_fail_on_deprecation = false;
        if ($cli_configuration->has_do_not_fail_on_deprecation()) {
            $do_not_fail_on_deprecation = $cli_configuration->do_not_fail_on_deprecation();
        }
        $do_not_fail_on_phpunit_deprecation = false;
        if ($cli_configuration->has_do_not_fail_on_phpunit_deprecation()) {
            $do_not_fail_on_phpunit_deprecation = $cli_configuration->do_not_fail_on_phpunit_deprecation();
        }
        $do_not_fail_on_phpunit_notice = false;
        if ($cli_configuration->has_do_not_fail_on_phpunit_notice()) {
            $do_not_fail_on_phpunit_notice = $cli_configuration->do_not_fail_on_phpunit_notice();
        }
        $do_not_fail_on_phpunit_warning = false;
        if ($cli_configuration->has_do_not_fail_on_phpunit_warning()) {
            $do_not_fail_on_phpunit_warning = $cli_configuration->do_not_fail_on_phpunit_warning();
        }
        $do_not_fail_on_empty_test_suite = false;
        if ($cli_configuration->has_do_not_fail_on_empty_test_suite()) {
            $do_not_fail_on_empty_test_suite = $cli_configuration->do_not_fail_on_empty_test_suite();
        }
        $do_not_fail_on_incomplete = false;
        if ($cli_configuration->has_do_not_fail_on_incomplete()) {
            $do_not_fail_on_incomplete = $cli_configuration->do_not_fail_on_incomplete();
        }
        $do_not_fail_on_notice = false;
        if ($cli_configuration->has_do_not_fail_on_notice()) {
            $do_not_fail_on_notice = $cli_configuration->do_not_fail_on_notice();
        }
        $do_not_fail_on_risky = false;
        if ($cli_configuration->has_do_not_fail_on_risky()) {
            $do_not_fail_on_risky = $cli_configuration->do_not_fail_on_risky();
        }
        $do_not_fail_on_skipped = false;
        if ($cli_configuration->has_do_not_fail_on_skipped()) {
            $do_not_fail_on_skipped = $cli_configuration->do_not_fail_on_skipped();
        }
        $do_not_fail_on_warning = false;
        if ($cli_configuration->has_do_not_fail_on_warning()) {
            $do_not_fail_on_warning = $cli_configuration->do_not_fail_on_warning();
        }
        if ($cli_configuration->has_stop_on_defect()) {
            $stop_on_defect = $cli_configuration->stop_on_defect();
        } else {
            $stop_on_defect = $xml_configuration->phpunit()->stop_on_defect();
        }
        if ($cli_configuration->has_stop_on_deprecation()) {
            $stop_on_deprecation = $cli_configuration->stop_on_deprecation();
        } else {
            $stop_on_deprecation = $xml_configuration->phpunit()->stop_on_deprecation();
        }
        $specific_deprecation_to_stop_on = null;
        if ($cli_configuration->has_specific_deprecation_to_stop_on()) {
            $specific_deprecation_to_stop_on = $cli_configuration->specific_deprecation_to_stop_on();
        }
        if ($cli_configuration->has_stop_on_error()) {
            $stop_on_error = $cli_configuration->stop_on_error();
        } else {
            $stop_on_error = $xml_configuration->phpunit()->stop_on_error();
        }
        if ($cli_configuration->has_stop_on_failure()) {
            $stop_on_failure = $cli_configuration->stop_on_failure();
        } else {
            $stop_on_failure = $xml_configuration->phpunit()->stop_on_failure();
        }
        if ($cli_configuration->has_stop_on_incomplete()) {
            $stop_on_incomplete = $cli_configuration->stop_on_incomplete();
        } else {
            $stop_on_incomplete = $xml_configuration->phpunit()->stop_on_incomplete();
        }
        if ($cli_configuration->has_stop_on_notice()) {
            $stop_on_notice = $cli_configuration->stop_on_notice();
        } else {
            $stop_on_notice = $xml_configuration->phpunit()->stop_on_notice();
        }
        if ($cli_configuration->has_stop_on_risky()) {
            $stop_on_risky = $cli_configuration->stop_on_risky();
        } else {
            $stop_on_risky = $xml_configuration->phpunit()->stop_on_risky();
        }
        if ($cli_configuration->has_stop_on_skipped()) {
            $stop_on_skipped = $cli_configuration->stop_on_skipped();
        } else {
            $stop_on_skipped = $xml_configuration->phpunit()->stop_on_skipped();
        }
        if ($cli_configuration->has_stop_on_warning()) {
            $stop_on_warning = $cli_configuration->stop_on_warning();
        } else {
            $stop_on_warning = $xml_configuration->phpunit()->stop_on_warning();
        }
        if ($cli_configuration->has_stderr() && $cli_configuration->stderr()) {
            $output_to_standard_error_stream = true;
        } else {
            $output_to_standard_error_stream = $xml_configuration->phpunit()->stderr();
        }
        if ($cli_configuration->has_columns()) {
            $columns = $cli_configuration->columns();
        } else {
            $columns = $xml_configuration->phpunit()->columns();
        }
        if ($columns === 'max') {
            $columns = (new Console())->get_number_of_columns();
        }
        if ($columns < 16) {
            $columns = 16;
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning('Less than 16 columns requested, number of columns set to 16');
        }
        assert(is_int($columns));
        $no_extensions = false;
        if ($cli_configuration->has_no_extensions() && $cli_configuration->no_extensions()) {
            $no_extensions = true;
        }
        $phar_extension_directory = null;
        if ($xml_configuration->phpunit()->has_extensions_directory()) {
            $phar_extension_directory = $xml_configuration->phpunit()->extensions_directory();
        }
        $extension_bootstrappers = [];
        if ($cli_configuration->has_extensions()) {
            foreach ($cli_configuration->extensions() as $extension) {
                $extension_bootstrappers[] = ['className' => $extension, 'parameters' => []];
            }
        }
        foreach ($xml_configuration->extensions() as $extension) {
            $extension_bootstrappers[] = ['className' => $extension->class_name(), 'parameters' => $extension->parameters()];
        }
        if ($cli_configuration->has_path_coverage() && $cli_configuration->path_coverage()) {
            $path_coverage = $cli_configuration->path_coverage();
        } else {
            $path_coverage = $xml_configuration->code_coverage()->path_coverage();
        }
        $default_colors = Colors::default();
        $default_thresholds = Thresholds::default();
        $coverage_clover = null;
        $coverage_cobertura = null;
        $coverage_crap4j = null;
        $coverage_crap4j_threshold = 30;
        $coverage_html = null;
        $coverage_html_low_upper_bound = $default_thresholds->low_upper_bound();
        $coverage_html_high_lower_bound = $default_thresholds->high_lower_bound();
        $coverage_html_color_success_low = $default_colors->success_low();
        $coverage_html_color_success_low_dark = $default_colors->success_low_dark();
        $coverage_html_color_success_medium = $default_colors->success_medium();
        $coverage_html_color_success_medium_dark = $default_colors->success_medium_dark();
        $coverage_html_color_success_high = $default_colors->success_high();
        $coverage_html_color_success_high_dark = $default_colors->success_high_dark();
        $coverage_html_color_success_bar = $default_colors->success_bar();
        $coverage_html_color_success_bar_dark = $default_colors->success_bar_dark();
        $coverage_html_color_warning = $default_colors->warning();
        $coverage_html_color_warning_dark = $default_colors->warning_dark();
        $coverage_html_color_warning_bar = $default_colors->warning_bar();
        $coverage_html_color_warning_bar_dark = $default_colors->warning_bar_dark();
        $coverage_html_color_danger = $default_colors->danger();
        $coverage_html_color_danger_dark = $default_colors->danger_dark();
        $coverage_html_color_danger_bar = $default_colors->danger_bar();
        $coverage_html_color_danger_bar_dark = $default_colors->danger_bar_dark();
        $coverage_html_color_breadcrumbs = $default_colors->breadcrumbs();
        $coverage_html_color_breadcrumbs_dark = $default_colors->breadcrumbs_dark();
        $coverage_html_custom_css_file = null;
        $coverage_open_clover = null;
        $coverage_php = null;
        $coverage_text = null;
        $coverage_text_show_uncovered_files = false;
        $coverage_text_show_only_summary = false;
        $coverage_xml = null;
        $coverage_xml_include_source = true;
        $coverage_from_xml_configuration = true;
        if ($cli_configuration->has_no_coverage() && $cli_configuration->no_coverage()) {
            $coverage_from_xml_configuration = false;
        }
        if ($cli_configuration->has_coverage_clover()) {
            $coverage_clover = $cli_configuration->coverage_clover();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_clover()) {
            $coverage_clover = $xml_configuration->code_coverage()->clover()->target()->path();
        }
        if ($cli_configuration->has_coverage_cobertura()) {
            $coverage_cobertura = $cli_configuration->coverage_cobertura();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_cobertura()) {
            $coverage_cobertura = $xml_configuration->code_coverage()->cobertura()->target()->path();
        }
        if ($xml_configuration->code_coverage()->has_crap4j()) {
            $coverage_crap4j_threshold = $xml_configuration->code_coverage()->crap4j()->threshold();
        }
        if ($cli_configuration->has_coverage_crap4j()) {
            $coverage_crap4j = $cli_configuration->coverage_crap4j();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_crap4j()) {
            $coverage_crap4j = $xml_configuration->code_coverage()->crap4j()->target()->path();
        }
        if ($xml_configuration->code_coverage()->has_html()) {
            $coverage_html_high_lower_bound = $xml_configuration->code_coverage()->html()->high_lower_bound();
            $coverage_html_low_upper_bound = $xml_configuration->code_coverage()->html()->low_upper_bound();
            if ($coverage_html_low_upper_bound > $coverage_html_high_lower_bound) {
                $coverage_html_low_upper_bound = $default_thresholds->low_upper_bound();
                $coverage_html_high_lower_bound = $default_thresholds->high_lower_bound();
            }
            $coverage_html_color_success_low = $xml_configuration->code_coverage()->html()->color_success_low();
            $coverage_html_color_success_low_dark = $xml_configuration->code_coverage()->html()->color_success_low_dark();
            $coverage_html_color_success_medium = $xml_configuration->code_coverage()->html()->color_success_medium();
            $coverage_html_color_success_medium_dark = $xml_configuration->code_coverage()->html()->color_success_medium_dark();
            $coverage_html_color_success_high = $xml_configuration->code_coverage()->html()->color_success_high();
            $coverage_html_color_success_high_dark = $xml_configuration->code_coverage()->html()->color_success_high_dark();
            $coverage_html_color_success_bar = $xml_configuration->code_coverage()->html()->color_success_bar();
            $coverage_html_color_success_bar_dark = $xml_configuration->code_coverage()->html()->color_success_bar_dark();
            $coverage_html_color_warning = $xml_configuration->code_coverage()->html()->color_warning();
            $coverage_html_color_warning_dark = $xml_configuration->code_coverage()->html()->color_warning_dark();
            $coverage_html_color_warning_bar = $xml_configuration->code_coverage()->html()->color_warning_bar();
            $coverage_html_color_warning_bar_dark = $xml_configuration->code_coverage()->html()->color_warning_bar_dark();
            $coverage_html_color_danger = $xml_configuration->code_coverage()->html()->color_danger();
            $coverage_html_color_danger_dark = $xml_configuration->code_coverage()->html()->color_danger_dark();
            $coverage_html_color_danger_bar = $xml_configuration->code_coverage()->html()->color_danger_bar();
            $coverage_html_color_danger_bar_dark = $xml_configuration->code_coverage()->html()->color_danger_bar_dark();
            $coverage_html_color_breadcrumbs = $xml_configuration->code_coverage()->html()->color_breadcrumbs();
            $coverage_html_color_breadcrumbs_dark = $xml_configuration->code_coverage()->html()->color_breadcrumbs_dark();
            if ($xml_configuration->code_coverage()->html()->has_custom_css_file()) {
                $coverage_html_custom_css_file = $xml_configuration->code_coverage()->html()->custom_css_file();
            }
        }
        if ($cli_configuration->has_coverage_html()) {
            $coverage_html = $cli_configuration->coverage_html();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_html() && $xml_configuration->code_coverage()->html()->has_target()) {
            $coverage_html = $xml_configuration->code_coverage()->html()->target()->path();
        }
        if ($cli_configuration->has_coverage_open_clover()) {
            $coverage_open_clover = $cli_configuration->coverage_open_clover();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_open_clover()) {
            $coverage_open_clover = $xml_configuration->code_coverage()->open_clover()->target()->path();
        }
        if ($cli_configuration->has_coverage_php()) {
            $coverage_php = $cli_configuration->coverage_php();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_php()) {
            $coverage_php = $xml_configuration->code_coverage()->php()->target()->path();
        }
        if ($xml_configuration->code_coverage()->has_text()) {
            $coverage_text_show_uncovered_files = $xml_configuration->code_coverage()->text()->show_uncovered_files();
            $coverage_text_show_only_summary = $xml_configuration->code_coverage()->text()->show_only_summary();
        }
        if ($cli_configuration->has_coverage_text_show_uncovered_files()) {
            $coverage_text_show_uncovered_files = $cli_configuration->coverage_text_show_uncovered_files();
        }
        if ($cli_configuration->has_coverage_text_show_only_summary()) {
            $coverage_text_show_only_summary = $cli_configuration->coverage_text_show_only_summary();
        }
        if ($cli_configuration->has_coverage_text()) {
            $coverage_text = $cli_configuration->coverage_text();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_text()) {
            $coverage_text = $xml_configuration->code_coverage()->text()->target()->path();
        }
        if ($cli_configuration->has_coverage_xml()) {
            $coverage_xml = $cli_configuration->coverage_xml();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_xml()) {
            $coverage_xml = $xml_configuration->code_coverage()->xml()->target()->path();
        }
        if ($cli_configuration->has_exclude_source_from_xml_coverage()) {
            $coverage_xml_include_source = !$cli_configuration->exclude_source_from_xml_coverage();
        } elseif ($coverage_from_xml_configuration && $xml_configuration->code_coverage()->has_xml()) {
            $coverage_xml_include_source = $xml_configuration->code_coverage()->xml()->include_source();
        }
        if ($cli_configuration->has_backup_globals()) {
            $backup_globals = $cli_configuration->backup_globals();
        } else {
            $backup_globals = $xml_configuration->phpunit()->backup_globals();
        }
        if ($cli_configuration->has_backup_static_properties()) {
            $backup_static_properties = $cli_configuration->backup_static_properties();
        } else {
            $backup_static_properties = $xml_configuration->phpunit()->backup_static_properties();
        }
        if ($cli_configuration->has_be_strict_about_changes_to_global_state()) {
            $be_strict_about_changes_to_global_state = $cli_configuration->be_strict_about_changes_to_global_state();
        } else {
            $be_strict_about_changes_to_global_state = $xml_configuration->phpunit()->be_strict_about_changes_to_global_state();
        }
        if ($cli_configuration->has_process_isolation()) {
            $process_isolation = $cli_configuration->process_isolation();
        } else {
            $process_isolation = $xml_configuration->phpunit()->process_isolation();
        }
        if ($cli_configuration->has_enforce_time_limit()) {
            $enforce_time_limit = $cli_configuration->enforce_time_limit();
        } else {
            $enforce_time_limit = $xml_configuration->phpunit()->enforce_time_limit();
        }
        if ($enforce_time_limit && !(new Invoker())->can_invoke_with_timeout()) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning('The pcntl extension is required for enforcing time limits');
        }
        if ($cli_configuration->has_default_time_limit()) {
            $default_time_limit = $cli_configuration->default_time_limit();
        } else {
            $default_time_limit = $xml_configuration->phpunit()->default_time_limit();
        }
        $timeout_for_small_tests = $xml_configuration->phpunit()->timeout_for_small_tests();
        $timeout_for_medium_tests = $xml_configuration->phpunit()->timeout_for_medium_tests();
        $timeout_for_large_tests = $xml_configuration->phpunit()->timeout_for_large_tests();
        if ($cli_configuration->has_report_useless_tests()) {
            $report_useless_tests = $cli_configuration->report_useless_tests();
        } else {
            $report_useless_tests = $xml_configuration->phpunit()->be_strict_about_tests_that_do_not_test_anything();
        }
        if ($cli_configuration->has_strict_coverage()) {
            $strict_coverage = $cli_configuration->strict_coverage();
        } else {
            $strict_coverage = $xml_configuration->phpunit()->be_strict_about_coverage_metadata();
        }
        if ($cli_configuration->has_disallow_test_output()) {
            $disallow_test_output = $cli_configuration->disallow_test_output();
        } else {
            $disallow_test_output = $xml_configuration->phpunit()->be_strict_about_output_during_tests();
        }
        if ($cli_configuration->has_display_details_on_all_issues()) {
            $display_details_on_all_issues = $cli_configuration->display_details_on_all_issues();
        } else {
            $display_details_on_all_issues = $xml_configuration->phpunit()->display_details_on_all_issues();
        }
        if ($cli_configuration->has_display_details_on_incomplete_tests()) {
            $display_details_on_incomplete_tests = $cli_configuration->display_details_on_incomplete_tests();
        } else {
            $display_details_on_incomplete_tests = $xml_configuration->phpunit()->display_details_on_incomplete_tests();
        }
        if ($cli_configuration->has_display_details_on_skipped_tests()) {
            $display_details_on_skipped_tests = $cli_configuration->display_details_on_skipped_tests();
        } else {
            $display_details_on_skipped_tests = $xml_configuration->phpunit()->display_details_on_skipped_tests();
        }
        if ($cli_configuration->has_display_details_on_tests_that_trigger_deprecations()) {
            $display_details_on_tests_that_trigger_deprecations = $cli_configuration->display_details_on_tests_that_trigger_deprecations();
        } else {
            $display_details_on_tests_that_trigger_deprecations = $xml_configuration->phpunit()->display_details_on_tests_that_trigger_deprecations();
        }
        if ($cli_configuration->has_display_details_on_phpunit_deprecations()) {
            $display_details_on_phpunit_deprecations = $cli_configuration->display_details_on_phpunit_deprecations();
        } else {
            $display_details_on_phpunit_deprecations = $xml_configuration->phpunit()->display_details_on_phpunit_deprecations();
        }
        if ($cli_configuration->has_display_details_on_phpunit_notices()) {
            $display_details_on_phpunit_notices = $cli_configuration->display_details_on_phpunit_notices();
        } else {
            $display_details_on_phpunit_notices = $xml_configuration->phpunit()->display_details_on_phpunit_notices();
        }
        if ($cli_configuration->has_display_details_on_tests_that_trigger_errors()) {
            $display_details_on_tests_that_trigger_errors = $cli_configuration->display_details_on_tests_that_trigger_errors();
        } else {
            $display_details_on_tests_that_trigger_errors = $xml_configuration->phpunit()->display_details_on_tests_that_trigger_errors();
        }
        if ($cli_configuration->has_display_details_on_tests_that_trigger_notices()) {
            $display_details_on_tests_that_trigger_notices = $cli_configuration->display_details_on_tests_that_trigger_notices();
        } else {
            $display_details_on_tests_that_trigger_notices = $xml_configuration->phpunit()->display_details_on_tests_that_trigger_notices();
        }
        if ($cli_configuration->has_display_details_on_tests_that_trigger_warnings()) {
            $display_details_on_tests_that_trigger_warnings = $cli_configuration->display_details_on_tests_that_trigger_warnings();
        } else {
            $display_details_on_tests_that_trigger_warnings = $xml_configuration->phpunit()->display_details_on_tests_that_trigger_warnings();
        }
        if ($cli_configuration->has_reverse_list()) {
            $reverse_defect_list = $cli_configuration->reverse_list();
        } else {
            $reverse_defect_list = $xml_configuration->phpunit()->reverse_defect_list();
        }
        $require_coverage_metadata = $xml_configuration->phpunit()->require_coverage_metadata();
        $require_sealed_mock_objects = $xml_configuration->phpunit()->require_sealed_mock_objects();
        if ($cli_configuration->has_execution_order()) {
            $execution_order = $cli_configuration->execution_order();
        } else {
            $execution_order = $xml_configuration->phpunit()->execution_order();
        }
        $execution_order_defects = Test_Suite_Sorter::ORDER_DEFAULT;
        if ($cli_configuration->has_execution_order_defects()) {
            $execution_order_defects = $cli_configuration->execution_order_defects();
        } elseif ($xml_configuration->phpunit()->defects_first()) {
            $execution_order_defects = Test_Suite_Sorter::ORDER_DEFECTS_FIRST;
        }
        if ($cli_configuration->has_resolve_dependencies()) {
            $resolve_dependencies = $cli_configuration->resolve_dependencies();
        } else {
            $resolve_dependencies = $xml_configuration->phpunit()->resolve_dependencies();
        }
        $colors = false;
        $colors_supported = (new Console())->has_color_support();
        if ($cli_configuration->has_colors()) {
            if ($cli_configuration->colors() === Configuration::COLOR_ALWAYS) {
                $colors = true;
            } elseif ($colors_supported && $cli_configuration->colors() === Configuration::COLOR_AUTO) {
                $colors = true;
            }
        } elseif ($xml_configuration->phpunit()->colors() === Configuration::COLOR_ALWAYS) {
            $colors = true;
        } elseif ($colors_supported && $xml_configuration->phpunit()->colors() === Configuration::COLOR_AUTO) {
            $colors = true;
        }
        $logfile_teamcity = null;
        $logfile_junit = null;
        $logfile_otr = null;
        $logfile_testdox_html = null;
        $logfile_testdox_text = null;
        $logging_from_xml_configuration = true;
        if ($cli_configuration->has_no_logging() && $cli_configuration->no_logging()) {
            $logging_from_xml_configuration = false;
        }
        if ($cli_configuration->has_teamcity_logfile()) {
            $logfile_teamcity = $cli_configuration->teamcity_logfile();
        } elseif ($logging_from_xml_configuration && $xml_configuration->logging()->has_team_city()) {
            $logfile_teamcity = $xml_configuration->logging()->team_city()->target()->path();
        }
        if ($cli_configuration->has_junit_logfile()) {
            $logfile_junit = $cli_configuration->junit_logfile();
        } elseif ($logging_from_xml_configuration && $xml_configuration->logging()->has_junit()) {
            $logfile_junit = $xml_configuration->logging()->junit()->target()->path();
        }
        if ($cli_configuration->has_otr_logfile()) {
            $logfile_otr = $cli_configuration->otr_logfile();
        } elseif ($logging_from_xml_configuration && $xml_configuration->logging()->has_otr()) {
            $logfile_otr = $xml_configuration->logging()->otr()->target()->path();
        }
        $include_git_information = false;
        $include_git_information_in_otr_logfile = false;
        if ($cli_configuration->has_include_git_information()) {
            $include_git_information = $cli_configuration->include_git_information();
            $include_git_information_in_otr_logfile = $cli_configuration->include_git_information();
        } elseif ($logging_from_xml_configuration && $xml_configuration->logging()->has_otr()) {
            $include_git_information_in_otr_logfile = $xml_configuration->logging()->otr()->include_git_information();
        }
        if ($cli_configuration->has_testdox_html_file()) {
            $logfile_testdox_html = $cli_configuration->testdox_html_file();
        } elseif ($logging_from_xml_configuration && $xml_configuration->logging()->has_test_dox_html()) {
            $logfile_testdox_html = $xml_configuration->logging()->test_dox_html()->target()->path();
        }
        if ($cli_configuration->has_testdox_text_file()) {
            $logfile_testdox_text = $cli_configuration->testdox_text_file();
        } elseif ($logging_from_xml_configuration && $xml_configuration->logging()->has_test_dox_text()) {
            $logfile_testdox_text = $xml_configuration->logging()->test_dox_text()->target()->path();
        }
        $log_events_text = null;
        if ($cli_configuration->has_log_events_text()) {
            $log_events_text = $cli_configuration->log_events_text();
        }
        $log_events_verbose_text = null;
        if ($cli_configuration->has_log_events_verbose_text()) {
            $log_events_verbose_text = $cli_configuration->log_events_verbose_text();
        }
        $team_city_output = false;
        if ($cli_configuration->has_team_city_printer() && $cli_configuration->team_city_printer()) {
            $team_city_output = true;
        }
        if ($cli_configuration->has_test_dox_printer() && $cli_configuration->testdox_printer()) {
            $test_dox_output = true;
        } else {
            $test_dox_output = $xml_configuration->phpunit()->testdox_printer();
        }
        if ($cli_configuration->has_test_dox_printer_summary() && $cli_configuration->testdox_printer_summary()) {
            $test_dox_output_summary = true;
        } else {
            $test_dox_output_summary = $xml_configuration->phpunit()->testdox_printer_summary();
        }
        $no_progress = false;
        if ($cli_configuration->has_no_progress() && $cli_configuration->no_progress()) {
            $no_progress = true;
        }
        $no_results = false;
        if ($cli_configuration->has_no_results() && $cli_configuration->no_results()) {
            $no_results = true;
        }
        $no_output = false;
        if ($cli_configuration->has_no_output() && $cli_configuration->no_output()) {
            $no_output = true;
        }
        $tests_covering = null;
        if ($cli_configuration->has_tests_covering()) {
            $tests_covering = $cli_configuration->tests_covering();
        }
        $tests_using = null;
        if ($cli_configuration->has_tests_using()) {
            $tests_using = $cli_configuration->tests_using();
        }
        $tests_requiring_php_extension = null;
        if ($cli_configuration->has_tests_requiring_php_extension()) {
            $tests_requiring_php_extension = $cli_configuration->tests_requiring_php_extension();
        }
        $filter = null;
        if ($cli_configuration->has_filter()) {
            $filter = $cli_configuration->filter();
        }
        $exclude_filter = null;
        if ($cli_configuration->has_exclude_filter()) {
            $exclude_filter = $cli_configuration->exclude_filter();
        }
        $ignore_test_selection_in_xml_configuration = false;
        if ($cli_configuration->has_all()) {
            $ignore_test_selection_in_xml_configuration = true;
        }
        $groups = [];
        if ($cli_configuration->has_groups()) {
            $groups = $cli_configuration->groups();
        } elseif (!$ignore_test_selection_in_xml_configuration) {
            $groups = $xml_configuration->groups()->include()->as_array_of_strings();
        }
        $exclude_groups = [];
        if ($cli_configuration->has_exclude_groups()) {
            $exclude_groups = $cli_configuration->exclude_groups();
        } elseif (!$ignore_test_selection_in_xml_configuration) {
            $exclude_groups = $xml_configuration->groups()->exclude()->as_array_of_strings();
        }
        $exclude_groups = array_diff($exclude_groups, $groups);
        if ($cli_configuration->has_random_order_seed()) {
            $random_order_seed = $cli_configuration->random_order_seed();
        } else {
            $random_order_seed = time();
        }
        if ($xml_configuration->was_loaded_from_file() && $xml_configuration->has_validation_errors()) {
            if ((new Schema_Detector())->detect($xml_configuration->filename())->detected()) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_deprecation('Your XML configuration validates against a deprecated schema. Migrate your XML configuration using "--migrate-configuration"!');
            } else {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning("Test results may not be as expected because the XML configuration file did not pass validation:\n" . $xml_configuration->validation_errors());
            }
        }
        $include_uncovered_files = $xml_configuration->code_coverage()->include_uncovered_files();
        $include_paths = [];
        if ($cli_configuration->has_include_path()) {
            foreach (explode(PATH_SEPARATOR, $cli_configuration->include_path()) as $include_path) {
                $include_paths[] = new Directory($include_path);
            }
        }
        foreach ($xml_configuration->php()->include_paths() as $include_path) {
            $include_paths[] = $include_path;
        }
        $ini_settings = [];
        if ($cli_configuration->has_ini_settings()) {
            foreach ($cli_configuration->ini_settings() as $name => $value) {
                $ini_settings[] = new Ini_Setting($name, $value);
            }
        }
        foreach ($xml_configuration->php()->ini_settings() as $ini_setting) {
            $ini_settings[] = $ini_setting;
        }
        $include_test_suite = '';
        if ($cli_configuration->has_test_suite()) {
            $include_test_suite = $cli_configuration->test_suite();
        } elseif ($xml_configuration->phpunit()->has_default_test_suite()) {
            $include_test_suite = $xml_configuration->phpunit()->default_test_suite();
        }
        $exclude_test_suite = '';
        if ($cli_configuration->has_excluded_test_suite()) {
            $exclude_test_suite = $cli_configuration->excluded_test_suite();
        }
        $test_suffixes = ['Test.php', '.phpt'];
        if ($cli_configuration->has_test_suffixes()) {
            $test_suffixes = $cli_configuration->test_suffixes();
        }
        $source_include_directories = [];
        if ($cli_configuration->has_coverage_filter()) {
            foreach ($cli_configuration->coverage_filter() as $directory) {
                $source_include_directories[] = new Filter_Directory($directory, '', '.php');
            }
        }
        foreach ($xml_configuration->source()->include_directories() as $directory) {
            $source_include_directories[] = $directory;
        }
        $source_include_files = $xml_configuration->source()->include_files();
        $source_exclude_directories = $xml_configuration->source()->exclude_directories();
        $source_exclude_files = $xml_configuration->source()->exclude_files();
        $use_baseline = null;
        $generate_baseline = null;
        if (!$cli_configuration->has_generate_baseline()) {
            if ($cli_configuration->has_use_baseline()) {
                $use_baseline = $cli_configuration->use_baseline();
            } elseif ($xml_configuration->source()->has_baseline()) {
                $use_baseline = $xml_configuration->source()->baseline();
            }
        } else {
            $generate_baseline = $cli_configuration->generate_baseline();
        }
        assert($use_baseline !== '');
        assert($generate_baseline !== '');
        if ($fail_on_all_issues) {
            $display_details_on_all_issues = true;
        }
        if ($fail_on_deprecation && !$do_not_fail_on_deprecation) {
            $display_details_on_tests_that_trigger_deprecations = true;
        }
        if ($fail_on_phpunit_deprecation && !$do_not_fail_on_phpunit_deprecation) {
            $display_details_on_phpunit_deprecations = true;
        }
        if ($fail_on_phpunit_notice && !$do_not_fail_on_phpunit_notice) {
            $display_details_on_phpunit_notices = true;
        }
        if ($fail_on_notice && !$do_not_fail_on_notice) {
            $display_details_on_tests_that_trigger_notices = true;
        }
        if ($fail_on_warning && !$do_not_fail_on_warning) {
            $display_details_on_tests_that_trigger_warnings = true;
        }
        if ($fail_on_incomplete && !$do_not_fail_on_incomplete) {
            $display_details_on_incomplete_tests = true;
        }
        if ($fail_on_skipped && !$do_not_fail_on_skipped) {
            $display_details_on_skipped_tests = true;
        }
        $issue_trigger_identification_needed = $xml_configuration->source()->ignore_self_deprecations() || $xml_configuration->source()->ignore_direct_deprecations() || $xml_configuration->source()->ignore_indirect_deprecations();
        if ($issue_trigger_identification_needed && !$xml_configuration->source()->identify_issue_trigger()) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning('The identification of issue triggers is disabled. However, ignoring self-deprecations, direct deprecations, or indirect deprecations is requested.');
        }
        return new Configuration($cli_configuration->arguments(), $test_files_file, $configuration_file, $bootstrap, $xml_configuration->phpunit()->bootstrap_for_test_suite(), $cache_result, $cache_directory, $coverage_cache_directory, new Source($use_baseline, $cli_configuration->ignore_baseline(), Filter_Directory_Collection::from_array($source_include_directories), $source_include_files, $source_exclude_directories, $source_exclude_files, $xml_configuration->source()->restrict_notices(), $xml_configuration->source()->restrict_warnings(), $xml_configuration->source()->ignore_suppression_of_deprecations(), $xml_configuration->source()->ignore_suppression_of_php_deprecations(), $xml_configuration->source()->ignore_suppression_of_errors(), $xml_configuration->source()->ignore_suppression_of_notices(), $xml_configuration->source()->ignore_suppression_of_php_notices(), $xml_configuration->source()->ignore_suppression_of_warnings(), $xml_configuration->source()->ignore_suppression_of_php_warnings(), $xml_configuration->source()->deprecation_triggers(), $xml_configuration->source()->ignore_self_deprecations(), $xml_configuration->source()->ignore_direct_deprecations(), $xml_configuration->source()->ignore_indirect_deprecations(), $xml_configuration->source()->identify_issue_trigger(), $xml_configuration->source()->issue_trigger_resolvers()), $test_result_cache_file, $coverage_clover, $coverage_cobertura, $coverage_crap4j, $coverage_crap4j_threshold, $coverage_html, $coverage_html_low_upper_bound, $coverage_html_high_lower_bound, $coverage_html_color_success_low, $coverage_html_color_success_low_dark, $coverage_html_color_success_medium, $coverage_html_color_success_medium_dark, $coverage_html_color_success_high, $coverage_html_color_success_high_dark, $coverage_html_color_success_bar, $coverage_html_color_success_bar_dark, $coverage_html_color_warning, $coverage_html_color_warning_dark, $coverage_html_color_warning_bar, $coverage_html_color_warning_bar_dark, $coverage_html_color_danger, $coverage_html_color_danger_dark, $coverage_html_color_danger_bar, $coverage_html_color_danger_bar_dark, $coverage_html_color_breadcrumbs, $coverage_html_color_breadcrumbs_dark, $coverage_html_custom_css_file, $coverage_open_clover, $coverage_php, $coverage_text, $coverage_text_show_uncovered_files, $coverage_text_show_only_summary, $coverage_xml, $coverage_xml_include_source, $path_coverage, $xml_configuration->code_coverage()->ignore_deprecated_code_units(), $disable_code_coverage_ignore, $fail_on_all_issues, $fail_on_deprecation, $fail_on_phpunit_deprecation, $fail_on_phpunit_notice, $fail_on_phpunit_warning, $fail_on_empty_test_suite, $fail_on_incomplete, $fail_on_notice, $fail_on_risky, $fail_on_skipped, $fail_on_warning, $do_not_fail_on_deprecation, $do_not_fail_on_phpunit_deprecation, $do_not_fail_on_phpunit_notice, $do_not_fail_on_phpunit_warning, $do_not_fail_on_empty_test_suite, $do_not_fail_on_incomplete, $do_not_fail_on_notice, $do_not_fail_on_risky, $do_not_fail_on_skipped, $do_not_fail_on_warning, $stop_on_defect, $stop_on_deprecation, $specific_deprecation_to_stop_on, $stop_on_error, $stop_on_failure, $stop_on_incomplete, $stop_on_notice, $stop_on_risky, $stop_on_skipped, $stop_on_warning, $output_to_standard_error_stream, $columns, $no_extensions, $phar_extension_directory, $extension_bootstrappers, $backup_globals, $backup_static_properties, $be_strict_about_changes_to_global_state, $colors, $process_isolation, $enforce_time_limit, $default_time_limit, $timeout_for_small_tests, $timeout_for_medium_tests, $timeout_for_large_tests, $report_useless_tests, $strict_coverage, $disallow_test_output, $display_details_on_all_issues, $display_details_on_incomplete_tests, $display_details_on_skipped_tests, $display_details_on_tests_that_trigger_deprecations, $display_details_on_phpunit_deprecations, $display_details_on_phpunit_notices, $display_details_on_tests_that_trigger_errors, $display_details_on_tests_that_trigger_notices, $display_details_on_tests_that_trigger_warnings, $reverse_defect_list, $require_coverage_metadata, $require_sealed_mock_objects, $no_progress, $no_results, $no_output, $execution_order, $execution_order_defects, $resolve_dependencies, $logfile_teamcity, $logfile_junit, $logfile_otr, $include_git_information, $include_git_information_in_otr_logfile, $logfile_testdox_html, $logfile_testdox_text, $log_events_text, $log_events_verbose_text, $team_city_output, $test_dox_output, $test_dox_output_summary, $tests_covering, $tests_using, $tests_requiring_php_extension, $filter, $exclude_filter, $groups, $exclude_groups, $random_order_seed, $include_uncovered_files, $xml_configuration->test_suite(), $include_test_suite, $exclude_test_suite, $xml_configuration->phpunit()->has_default_test_suite() ? $xml_configuration->phpunit()->default_test_suite() : null, $ignore_test_selection_in_xml_configuration, $test_suffixes, new Php(Directory_Collection::from_array($include_paths), Ini_Setting_Collection::from_array($ini_settings), $xml_configuration->php()->constants(), $xml_configuration->php()->global_variables(), $xml_configuration->php()->env_variables(), $xml_configuration->php()->post_variables(), $xml_configuration->php()->get_variables(), $xml_configuration->php()->cookie_variables(), $xml_configuration->php()->server_variables(), $xml_configuration->php()->files_variables(), $xml_configuration->php()->request_variables()), $xml_configuration->phpunit()->control_garbage_collector(), $xml_configuration->phpunit()->number_of_tests_before_garbage_collection(), $generate_baseline, $cli_configuration->debug(), $cli_configuration->with_telemetry(), $xml_configuration->phpunit()->shorten_arrays_for_export_threshold());
    }
}