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

use function assert;
use function basename;
use const DIRECTORY_SEPARATOR;
use function explode;
use function getcwd;
use function is_file;
use function is_numeric;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Runner\Test_Suite_Sorter;
use Php_Unit\Util\Filesystem;
use Sebastian_Bergmann\Cli_Parser\Exception as CliParserException;
use Sebastian_Bergmann\Cli_Parser\Parser as CliParser;
use function sprintf;
use function strtolower;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Builder
{
    /**
     * @var non-empty-list<non-empty-string>
     */
    private const array LONG_OPTIONS = ['all', 'atleast-version=', 'bootstrap=', 'cache-result', 'do-not-cache-result', 'cache-directory=', 'check-version', 'check-php-configuration', 'colors==', 'columns=', 'configuration=', 'warm-coverage-cache', 'coverage-filter=', 'coverage-clover=', 'coverage-cobertura=', 'coverage-crap4j=', 'coverage-html=', 'coverage-openclover=', 'coverage-php=', 'coverage-text==', 'only-summary-for-coverage-text', 'show-uncovered-for-coverage-text', 'coverage-xml=', 'exclude-source-from-xml-coverage', 'path-coverage', 'disallow-test-output', 'display-all-issues', 'display-incomplete', 'display-skipped', 'display-deprecations', 'display-phpunit-deprecations', 'display-phpunit-notices', 'display-errors', 'display-notices', 'display-warnings', 'default-time-limit=', 'enforce-time-limit', 'exclude-group=', 'filter=', 'exclude-filter=', 'generate-baseline=', 'use-baseline=', 'ignore-baseline', 'generate-configuration', 'globals-backup', 'group=', 'covers=', 'uses=', 'requires-php-extension=', 'help', 'resolve-dependencies', 'ignore-dependencies', 'include-path=', 'list-groups', 'list-suites', 'list-test-files', 'list-tests', 'list-tests-xml=', 'log-junit=', 'log-otr=', 'include-git-information', 'log-teamcity=', 'migrate-configuration', 'no-configuration', 'no-coverage', 'no-logging', 'no-extensions', 'no-output', 'no-progress', 'no-results', 'order-by=', 'process-isolation', 'do-not-report-useless-tests', 'random-order', 'random-order-seed=', 'reverse-order', 'reverse-list', 'static-backup', 'stderr', 'fail-on-all-issues', 'fail-on-deprecation', 'fail-on-phpunit-deprecation', 'fail-on-phpunit-notice', 'fail-on-phpunit-warning', 'fail-on-empty-test-suite', 'fail-on-incomplete', 'fail-on-notice', 'fail-on-risky', 'fail-on-skipped', 'fail-on-warning', 'do-not-fail-on-deprecation', 'do-not-fail-on-phpunit-deprecation', 'do-not-fail-on-phpunit-notice', 'do-not-fail-on-phpunit-warning', 'do-not-fail-on-empty-test-suite', 'do-not-fail-on-incomplete', 'do-not-fail-on-notice', 'do-not-fail-on-risky', 'do-not-fail-on-skipped', 'do-not-fail-on-warning', 'stop-on-defect', 'stop-on-deprecation==', 'stop-on-error', 'stop-on-failure', 'stop-on-incomplete', 'stop-on-notice', 'stop-on-risky', 'stop-on-skipped', 'stop-on-warning', 'strict-coverage', 'disable-coverage-ignore', 'strict-global-state', 'teamcity', 'testdox', 'testdox-summary', 'testdox-html=', 'testdox-text=', 'test-suffix=', 'testsuite=', 'exclude-testsuite=', 'test-files-file=', 'log-events-text=', 'log-events-verbose-text=', 'version', 'debug', 'with-telemetry', 'extension='];
    private const string SHORT_OPTIONS = 'd:c:h';
    /**
     * @var array<string, non-negative-int>
     */
    private array $processed = [];
    /**
     * @param list<string> $parameters
     *
     * @throws Exception
     */
    public function from_parameters(array $parameters): Configuration
    {
        try {
            $options = (new Cli_Parser())->parse($parameters, self::SHORT_OPTIONS, self::LONG_OPTIONS);
        } catch (Cli_Parser_Exception $e) {
            throw new Exception($e->get_message(), $e->get_code(), $e);
        }
        $all = null;
        $at_least_version = null;
        $backup_globals = null;
        $backup_static_properties = null;
        $be_strict_about_changes_to_global_state = null;
        $bootstrap = null;
        $cache_directory = null;
        $cache_result = null;
        $check_php_configuration = false;
        $check_version = false;
        $colors = null;
        $columns = null;
        $configuration = null;
        $warm_coverage_cache = false;
        $coverage_filter = null;
        $coverage_clover = null;
        $coverage_cobertura = null;
        $coverage_crap4j = null;
        $coverage_html = null;
        $coverage_open_clover = null;
        $coverage_php = null;
        $coverage_text = null;
        $coverage_text_show_uncovered_files = null;
        $coverage_text_show_only_summary = null;
        $coverage_xml = null;
        $exclude_source_from_xml_coverage = null;
        $path_coverage = null;
        $default_time_limit = null;
        $disable_code_coverage_ignore = null;
        $disallow_test_output = null;
        $display_all_issues = null;
        $display_incomplete = null;
        $display_skipped = null;
        $display_deprecations = null;
        $display_phpunit_deprecations = null;
        $display_phpunit_notices = null;
        $display_errors = null;
        $display_notices = null;
        $display_warnings = null;
        $enforce_time_limit = null;
        $exclude_groups = null;
        $execution_order = null;
        $execution_order_defects = null;
        $fail_on_all_issues = null;
        $fail_on_deprecation = null;
        $fail_on_phpunit_deprecation = null;
        $fail_on_phpunit_notice = null;
        $fail_on_phpunit_warning = null;
        $fail_on_empty_test_suite = null;
        $fail_on_incomplete = null;
        $fail_on_notice = null;
        $fail_on_risky = null;
        $fail_on_skipped = null;
        $fail_on_warning = null;
        $do_not_fail_on_deprecation = null;
        $do_not_fail_on_phpunit_deprecation = null;
        $do_not_fail_on_phpunit_notice = null;
        $do_not_fail_on_phpunit_warning = null;
        $do_not_fail_on_empty_test_suite = null;
        $do_not_fail_on_incomplete = null;
        $do_not_fail_on_notice = null;
        $do_not_fail_on_risky = null;
        $do_not_fail_on_skipped = null;
        $do_not_fail_on_warning = null;
        $stop_on_defect = null;
        $stop_on_deprecation = null;
        $specific_deprecation_to_stop_on = null;
        $stop_on_error = null;
        $stop_on_failure = null;
        $stop_on_incomplete = null;
        $stop_on_notice = null;
        $stop_on_risky = null;
        $stop_on_skipped = null;
        $stop_on_warning = null;
        $filter = null;
        $exclude_filter = null;
        $generate_baseline = null;
        $use_baseline = null;
        $ignore_baseline = false;
        $generate_configuration = false;
        $migrate_configuration = false;
        $groups = null;
        $tests_covering = null;
        $tests_using = null;
        $tests_requiring_php_extension = null;
        $help = false;
        $include_path = null;
        $ini_settings = [];
        $junit_logfile = null;
        $otr_logfile = null;
        $include_git_information = null;
        $list_groups = false;
        $list_suites = false;
        $list_test_files = false;
        $list_tests = false;
        $list_tests_xml = null;
        $no_coverage = null;
        $no_extensions = null;
        $no_output = null;
        $no_progress = null;
        $no_results = null;
        $no_logging = null;
        $process_isolation = null;
        $random_order_seed = null;
        $report_useless_tests = null;
        $resolve_dependencies = null;
        $reverse_list = null;
        $stderr = null;
        $strict_coverage = null;
        $teamcity_logfile = null;
        $testdox_html_file = null;
        $testdox_text_file = null;
        $test_suffixes = null;
        $test_suite = null;
        $exclude_test_suite = null;
        $test_files_file = null;
        $use_default_configuration = true;
        $version = false;
        $log_events_text = null;
        $log_events_verbose_text = null;
        $printer_team_city = null;
        $printer_test_dox = null;
        $printer_test_dox_summary = null;
        $debug = false;
        $with_telemetry = false;
        $extensions = [];
        foreach ($options[0] as $option) {
            $option_allowed_multiple_times = false;
            switch ($option[0]) {
                case '--all':
                    $all = true;
                    break;
                case '--colors':
                    $colors = \Php_Unit\Text_Ui\Configuration\Configuration::COLOR_AUTO;
                    if ($option[1] !== null) {
                        $colors = $option[1];
                    }
                    break;
                case '--bootstrap':
                    $bootstrap = $option[1];
                    break;
                case '--cache-directory':
                    $cache_directory = $option[1];
                    break;
                case '--cache-result':
                    $cache_result = true;
                    break;
                case '--do-not-cache-result':
                    $cache_result = false;
                    break;
                case '--columns':
                    if (is_numeric($option[1])) {
                        $columns = (int) $option[1];
                    } elseif ($option[1] === 'max') {
                        $columns = 'max';
                    }
                    break;
                case 'c':
                case '--configuration':
                    $configuration = $option[1];
                    break;
                case '--warm-coverage-cache':
                    $warm_coverage_cache = true;
                    break;
                case '--coverage-clover':
                    $coverage_clover = $option[1];
                    break;
                case '--coverage-cobertura':
                    $coverage_cobertura = $option[1];
                    break;
                case '--coverage-crap4j':
                    $coverage_crap4j = $option[1];
                    break;
                case '--coverage-html':
                    $coverage_html = $option[1];
                    break;
                case '--coverage-php':
                    $coverage_php = $option[1];
                    break;
                case '--coverage-openclover':
                    $coverage_open_clover = $option[1];
                    break;
                case '--coverage-text':
                    if ($option[1] === null) {
                        $option[1] = 'php://stdout';
                    }
                    $coverage_text = $option[1];
                    break;
                case '--only-summary-for-coverage-text':
                    $coverage_text_show_only_summary = true;
                    break;
                case '--show-uncovered-for-coverage-text':
                    $coverage_text_show_uncovered_files = true;
                    break;
                case '--coverage-xml':
                    $coverage_xml = $option[1];
                    break;
                case '--exclude-source-from-xml-coverage':
                    $exclude_source_from_xml_coverage = true;
                    break;
                case '--path-coverage':
                    $path_coverage = true;
                    break;
                case 'd':
                    $tmp = explode('=', (string) $option[1]);
                    if (isset($tmp[0])) {
                        assert($tmp[0] !== '');
                        if (isset($tmp[1])) {
                            assert($tmp[1] !== '');
                            $ini_settings[$tmp[0]] = $tmp[1];
                        } else {
                            $ini_settings[$tmp[0]] = '1';
                        }
                    }
                    $option_allowed_multiple_times = true;
                    break;
                case 'h':
                case '--help':
                    $help = true;
                    break;
                case '--filter':
                    $filter = $option[1];
                    break;
                case '--exclude-filter':
                    $exclude_filter = $option[1];
                    break;
                case '--testsuite':
                    $test_suite = $option[1];
                    break;
                case '--exclude-testsuite':
                    $exclude_test_suite = $option[1];
                    break;
                case '--test-files-file':
                    $test_files_file = $option[1];
                    break;
                case '--generate-baseline':
                    $generate_baseline = $option[1];
                    if (basename((string) $generate_baseline) === $generate_baseline) {
                        $generate_baseline = getcwd() . DIRECTORY_SEPARATOR . $generate_baseline;
                    }
                    break;
                case '--use-baseline':
                    $use_baseline = $option[1];
                    if (basename((string) $use_baseline) === $use_baseline && !is_file($use_baseline)) {
                        $use_baseline = getcwd() . DIRECTORY_SEPARATOR . $use_baseline;
                    }
                    break;
                case '--ignore-baseline':
                    $ignore_baseline = true;
                    break;
                case '--generate-configuration':
                    $generate_configuration = true;
                    break;
                case '--migrate-configuration':
                    $migrate_configuration = true;
                    break;
                case '--group':
                    if ($groups === null) {
                        $groups = [];
                    }
                    $groups[] = $option[1];
                    $option_allowed_multiple_times = true;
                    break;
                case '--exclude-group':
                    if ($exclude_groups === null) {
                        $exclude_groups = [];
                    }
                    $exclude_groups[] = $option[1];
                    $option_allowed_multiple_times = true;
                    break;
                case '--covers':
                    if ($tests_covering === null) {
                        $tests_covering = [];
                    }
                    $tests_covering[] = strtolower((string) $option[1]);
                    $option_allowed_multiple_times = true;
                    break;
                case '--uses':
                    if ($tests_using === null) {
                        $tests_using = [];
                    }
                    $tests_using[] = strtolower((string) $option[1]);
                    $option_allowed_multiple_times = true;
                    break;
                case '--requires-php-extension':
                    if ($tests_requiring_php_extension === null) {
                        $tests_requiring_php_extension = [];
                    }
                    $tests_requiring_php_extension[] = strtolower((string) $option[1]);
                    $option_allowed_multiple_times = true;
                    break;
                case '--test-suffix':
                    if ($test_suffixes === null) {
                        $test_suffixes = [];
                    }
                    $test_suffixes[] = $option[1];
                    $option_allowed_multiple_times = true;
                    break;
                case '--include-path':
                    $include_path = $option[1];
                    break;
                case '--list-groups':
                    $list_groups = true;
                    break;
                case '--list-suites':
                    $list_suites = true;
                    break;
                case '--list-test-files':
                    $list_test_files = true;
                    break;
                case '--list-tests':
                    $list_tests = true;
                    break;
                case '--list-tests-xml':
                    $list_tests_xml = $option[1];
                    break;
                case '--log-junit':
                    $junit_logfile = $option[1];
                    break;
                case '--log-otr':
                    $otr_logfile = $option[1];
                    break;
                case '--include-git-information':
                    $include_git_information = true;
                    break;
                case '--log-teamcity':
                    $teamcity_logfile = $option[1];
                    break;
                case '--order-by':
                    foreach (explode(',', (string) $option[1]) as $order) {
                        switch ($order) {
                            case 'default':
                                $execution_order = Test_Suite_Sorter::ORDER_DEFAULT;
                                $execution_order_defects = Test_Suite_Sorter::ORDER_DEFAULT;
                                $resolve_dependencies = true;
                                break;
                            case 'defects':
                                $execution_order_defects = Test_Suite_Sorter::ORDER_DEFECTS_FIRST;
                                break;
                            case 'depends':
                                $resolve_dependencies = true;
                                break;
                            case 'duration':
                                $execution_order = Test_Suite_Sorter::ORDER_DURATION;
                                break;
                            case 'no-depends':
                                $resolve_dependencies = false;
                                break;
                            case 'random':
                                $execution_order = Test_Suite_Sorter::ORDER_RANDOMIZED;
                                break;
                            case 'reverse':
                                $execution_order = Test_Suite_Sorter::ORDER_REVERSED;
                                break;
                            case 'size':
                                $execution_order = Test_Suite_Sorter::ORDER_SIZE;
                                break;
                            default:
                                throw new Exception(sprintf('unrecognized --order-by option: %s', $order));
                        }
                    }
                    break;
                case '--process-isolation':
                    $process_isolation = true;
                    break;
                case '--stderr':
                    $stderr = true;
                    break;
                case '--fail-on-all-issues':
                    $fail_on_all_issues = true;
                    break;
                case '--fail-on-deprecation':
                    $this->warn_when_options_conflict($do_not_fail_on_deprecation, '--fail-on-deprecation', '--do-not-fail-on-deprecation');
                    $fail_on_deprecation = true;
                    break;
                case '--fail-on-phpunit-deprecation':
                    $this->warn_when_options_conflict($do_not_fail_on_phpunit_deprecation, '--fail-on-phpunit-deprecation', '--do-not-fail-on-phpunit-deprecation');
                    $fail_on_phpunit_deprecation = true;
                    break;
                case '--fail-on-phpunit-notice':
                    $this->warn_when_options_conflict($do_not_fail_on_phpunit_notice, '--fail-on-phpunit-notice', '--do-not-fail-on-phpunit-notice');
                    $fail_on_phpunit_notice = true;
                    break;
                case '--fail-on-phpunit-warning':
                    $this->warn_when_options_conflict($do_not_fail_on_phpunit_warning, '--fail-on-phpunit-warning', '--do-not-fail-on-phpunit-warning');
                    $fail_on_phpunit_warning = true;
                    break;
                case '--fail-on-empty-test-suite':
                    $this->warn_when_options_conflict($do_not_fail_on_empty_test_suite, '--fail-on-empty-test-suite', '--do-not-fail-on-empty-test-suite');
                    $fail_on_empty_test_suite = true;
                    break;
                case '--fail-on-incomplete':
                    $this->warn_when_options_conflict($do_not_fail_on_incomplete, '--fail-on-incomplete', '--do-not-fail-on-incomplete');
                    $fail_on_incomplete = true;
                    break;
                case '--fail-on-notice':
                    $this->warn_when_options_conflict($do_not_fail_on_notice, '--fail-on-notice', '--do-not-fail-on-notice');
                    $fail_on_notice = true;
                    break;
                case '--fail-on-risky':
                    $this->warn_when_options_conflict($do_not_fail_on_risky, '--fail-on-risky', '--do-not-fail-on-risky');
                    $fail_on_risky = true;
                    break;
                case '--fail-on-skipped':
                    $this->warn_when_options_conflict($do_not_fail_on_skipped, '--fail-on-skipped', '--do-not-fail-on-skipped');
                    $fail_on_skipped = true;
                    break;
                case '--fail-on-warning':
                    $this->warn_when_options_conflict($do_not_fail_on_warning, '--fail-on-warning', '--do-not-fail-on-warning');
                    $fail_on_warning = true;
                    break;
                case '--do-not-fail-on-deprecation':
                    $this->warn_when_options_conflict($fail_on_deprecation, '--do-not-fail-on-deprecation', '--fail-on-deprecation');
                    $do_not_fail_on_deprecation = true;
                    break;
                case '--do-not-fail-on-phpunit-deprecation':
                    $this->warn_when_options_conflict($fail_on_phpunit_deprecation, '--do-not-fail-on-phpunit-deprecation', '--fail-on-phpunit-deprecation');
                    $do_not_fail_on_phpunit_deprecation = true;
                    break;
                case '--do-not-fail-on-phpunit-notice':
                    $this->warn_when_options_conflict($fail_on_phpunit_notice, '--do-not-fail-on-phpunit-notice', '--fail-on-phpunit-notice');
                    $do_not_fail_on_phpunit_notice = true;
                    break;
                case '--do-not-fail-on-phpunit-warning':
                    $this->warn_when_options_conflict($fail_on_phpunit_warning, '--do-not-fail-on-phpunit-warning', '--fail-on-phpunit-warning');
                    $do_not_fail_on_phpunit_warning = true;
                    break;
                case '--do-not-fail-on-empty-test-suite':
                    $this->warn_when_options_conflict($fail_on_empty_test_suite, '--do-not-fail-on-empty-test-suite', '--fail-on-empty-test-suite');
                    $do_not_fail_on_empty_test_suite = true;
                    break;
                case '--do-not-fail-on-incomplete':
                    $this->warn_when_options_conflict($fail_on_incomplete, '--do-not-fail-on-incomplete', '--fail-on-incomplete');
                    $do_not_fail_on_incomplete = true;
                    break;
                case '--do-not-fail-on-notice':
                    $this->warn_when_options_conflict($fail_on_notice, '--do-not-fail-on-notice', '--fail-on-notice');
                    $do_not_fail_on_notice = true;
                    break;
                case '--do-not-fail-on-risky':
                    $this->warn_when_options_conflict($fail_on_risky, '--do-not-fail-on-risky', '--fail-on-risky');
                    $do_not_fail_on_risky = true;
                    break;
                case '--do-not-fail-on-skipped':
                    $this->warn_when_options_conflict($fail_on_skipped, '--do-not-fail-on-skipped', '--fail-on-skipped');
                    $do_not_fail_on_skipped = true;
                    break;
                case '--do-not-fail-on-warning':
                    $this->warn_when_options_conflict($fail_on_warning, '--do-not-fail-on-warning', '--fail-on-warning');
                    $do_not_fail_on_warning = true;
                    break;
                case '--stop-on-defect':
                    $stop_on_defect = true;
                    break;
                case '--stop-on-deprecation':
                    $stop_on_deprecation = true;
                    if ($option[1] !== null) {
                        $specific_deprecation_to_stop_on = $option[1];
                    }
                    break;
                case '--stop-on-error':
                    $stop_on_error = true;
                    break;
                case '--stop-on-failure':
                    $stop_on_failure = true;
                    break;
                case '--stop-on-incomplete':
                    $stop_on_incomplete = true;
                    break;
                case '--stop-on-notice':
                    $stop_on_notice = true;
                    break;
                case '--stop-on-risky':
                    $stop_on_risky = true;
                    break;
                case '--stop-on-skipped':
                    $stop_on_skipped = true;
                    break;
                case '--stop-on-warning':
                    $stop_on_warning = true;
                    break;
                case '--teamcity':
                    $printer_team_city = true;
                    break;
                case '--testdox':
                    $printer_test_dox = true;
                    break;
                case '--testdox-summary':
                    $printer_test_dox_summary = true;
                    break;
                case '--testdox-html':
                    $testdox_html_file = $option[1];
                    break;
                case '--testdox-text':
                    $testdox_text_file = $option[1];
                    break;
                case '--no-configuration':
                    $use_default_configuration = false;
                    break;
                case '--no-extensions':
                    $no_extensions = true;
                    break;
                case '--no-coverage':
                    $no_coverage = true;
                    break;
                case '--no-logging':
                    $no_logging = true;
                    break;
                case '--no-output':
                    $no_output = true;
                    break;
                case '--no-progress':
                    $no_progress = true;
                    break;
                case '--no-results':
                    $no_results = true;
                    break;
                case '--globals-backup':
                    $backup_globals = true;
                    break;
                case '--static-backup':
                    $backup_static_properties = true;
                    break;
                case '--atleast-version':
                    $at_least_version = $option[1];
                    break;
                case '--version':
                    $version = true;
                    break;
                case '--do-not-report-useless-tests':
                    $report_useless_tests = false;
                    break;
                case '--strict-coverage':
                    $strict_coverage = true;
                    break;
                case '--disable-coverage-ignore':
                    $disable_code_coverage_ignore = true;
                    break;
                case '--strict-global-state':
                    $be_strict_about_changes_to_global_state = true;
                    break;
                case '--disallow-test-output':
                    $disallow_test_output = true;
                    break;
                case '--display-all-issues':
                    $display_all_issues = true;
                    break;
                case '--display-incomplete':
                    $display_incomplete = true;
                    break;
                case '--display-skipped':
                    $display_skipped = true;
                    break;
                case '--display-deprecations':
                    $display_deprecations = true;
                    break;
                case '--display-phpunit-deprecations':
                    $display_phpunit_deprecations = true;
                    break;
                case '--display-phpunit-notices':
                    $display_phpunit_notices = true;
                    break;
                case '--display-errors':
                    $display_errors = true;
                    break;
                case '--display-notices':
                    $display_notices = true;
                    break;
                case '--display-warnings':
                    $display_warnings = true;
                    break;
                case '--default-time-limit':
                    $default_time_limit = (int) $option[1];
                    break;
                case '--enforce-time-limit':
                    $enforce_time_limit = true;
                    break;
                case '--reverse-list':
                    $reverse_list = true;
                    break;
                case '--check-php-configuration':
                    $check_php_configuration = true;
                    break;
                case '--check-version':
                    $check_version = true;
                    break;
                case '--coverage-filter':
                    if ($coverage_filter === null) {
                        $coverage_filter = [];
                    }
                    $coverage_filter[] = $option[1];
                    $option_allowed_multiple_times = true;
                    break;
                case '--random-order':
                    $execution_order = Test_Suite_Sorter::ORDER_RANDOMIZED;
                    break;
                case '--random-order-seed':
                    $random_order_seed = (int) $option[1];
                    break;
                case '--resolve-dependencies':
                    $resolve_dependencies = true;
                    break;
                case '--ignore-dependencies':
                    $resolve_dependencies = false;
                    break;
                case '--reverse-order':
                    $execution_order = Test_Suite_Sorter::ORDER_REVERSED;
                    break;
                case '--log-events-text':
                    $log_events_text = Filesystem::resolve_stream_or_file($option[1]);
                    if ($log_events_text === false) {
                        throw new Exception(sprintf('The path "%s" specified for the --log-events-text option could not be resolved', $option[1]));
                    }
                    break;
                case '--log-events-verbose-text':
                    $log_events_verbose_text = Filesystem::resolve_stream_or_file($option[1]);
                    if ($log_events_verbose_text === false) {
                        throw new Exception(sprintf('The path "%s" specified for the --log-events-verbose-text option could not be resolved', $option[1]));
                    }
                    break;
                case '--debug':
                    $debug = true;
                    break;
                case '--with-telemetry':
                    $with_telemetry = true;
                    break;
                case '--extension':
                    $extensions[] = $option[1];
                    $option_allowed_multiple_times = true;
                    break;
            }
            if (!$option_allowed_multiple_times) {
                $this->mark_processed($option[0]);
            }
        }
        if ($ini_settings === []) {
            $ini_settings = null;
        }
        if ($extensions === []) {
            $extensions = null;
        }
        return new Configuration($options[1], $test_files_file, $all, $at_least_version, $backup_globals, $backup_static_properties, $be_strict_about_changes_to_global_state, $bootstrap, $cache_directory, $cache_result, $check_php_configuration, $check_version, $colors, $columns, $configuration, $coverage_clover, $coverage_cobertura, $coverage_crap4j, $coverage_html, $coverage_open_clover, $coverage_php, $coverage_text, $coverage_text_show_uncovered_files, $coverage_text_show_only_summary, $coverage_xml, $exclude_source_from_xml_coverage, $path_coverage, $warm_coverage_cache, $default_time_limit, $disable_code_coverage_ignore, $disallow_test_output, $enforce_time_limit, $exclude_groups, $execution_order, $execution_order_defects, $fail_on_all_issues, $fail_on_deprecation, $fail_on_phpunit_deprecation, $fail_on_phpunit_notice, $fail_on_phpunit_warning, $fail_on_empty_test_suite, $fail_on_incomplete, $fail_on_notice, $fail_on_risky, $fail_on_skipped, $fail_on_warning, $do_not_fail_on_deprecation, $do_not_fail_on_phpunit_deprecation, $do_not_fail_on_phpunit_notice, $do_not_fail_on_phpunit_warning, $do_not_fail_on_empty_test_suite, $do_not_fail_on_incomplete, $do_not_fail_on_notice, $do_not_fail_on_risky, $do_not_fail_on_skipped, $do_not_fail_on_warning, $stop_on_defect, $stop_on_deprecation, $specific_deprecation_to_stop_on, $stop_on_error, $stop_on_failure, $stop_on_incomplete, $stop_on_notice, $stop_on_risky, $stop_on_skipped, $stop_on_warning, $filter, $exclude_filter, $generate_baseline, $use_baseline, $ignore_baseline, $generate_configuration, $migrate_configuration, $groups, $tests_covering, $tests_using, $tests_requiring_php_extension, $help, $include_path, $ini_settings, $junit_logfile, $otr_logfile, $include_git_information, $list_groups, $list_suites, $list_test_files, $list_tests, $list_tests_xml, $no_coverage, $no_extensions, $no_output, $no_progress, $no_results, $no_logging, $process_isolation, $random_order_seed, $report_useless_tests, $resolve_dependencies, $reverse_list, $stderr, $strict_coverage, $teamcity_logfile, $testdox_html_file, $testdox_text_file, $test_suffixes, $test_suite, $exclude_test_suite, $use_default_configuration, $display_all_issues, $display_incomplete, $display_skipped, $display_deprecations, $display_phpunit_deprecations, $display_phpunit_notices, $display_errors, $display_notices, $display_warnings, $version, $coverage_filter, $log_events_text, $log_events_verbose_text, $printer_team_city, $printer_test_dox, $printer_test_dox_summary, $debug, $with_telemetry, $extensions);
    }
    /**
     * @param non-empty-string $option
     */
    private function mark_processed(string $option): void
    {
        if (!isset($this->processed[$option])) {
            $this->processed[$option] = 1;
            return;
        }
        $this->processed[$option]++;
        if ($this->processed[$option] === 2) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Option %s cannot be used more than once', $option));
        }
    }
    /**
     * @param non-empty-string $option
     */
    private function warn_when_options_conflict(?bool $current, string $option, string $opposite): void
    {
        if ($current === null) {
            return;
        }
        Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Options %s and %s cannot be used together', $option, $opposite));
    }
}