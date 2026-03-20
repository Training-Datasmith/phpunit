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
namespace Php_Unit\Text_Ui;

use function array_reverse;
use function assert;
use function class_exists;
use function class_implements;
use function defined;
use function dirname;
use function explode;
use function function_exists;
use function in_array;
use function is_file;
use function method_exists;
use const PHP_EOL;
use const PHP_VERSION;
use Php_Unit\Event\Event_Facade_Is_Sealed_Exception;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Event\Unknown_Subscriber_Type_Exception;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Logging\Event_Logger;
use Php_Unit\Logging\J_Unit\Junit_Xml_Logger;
use Php_Unit\Logging\Open_Test_Reporting\Cannot_Open_Uri_For_Writing_Exception;
use Php_Unit\Logging\Open_Test_Reporting\Otr_Xml_Logger;
use Php_Unit\Logging\Team_City\Team_City_Logger;
use Php_Unit\Logging\Test_Dox\Html_Renderer as TestDoxHtmlRenderer;
use Php_Unit\Logging\Test_Dox\Plain_Text_Renderer as TestDoxTextRenderer;
use Php_Unit\Logging\Test_Dox\Test_Result_Collector as TestDoxResultCollector;
use Php_Unit\Runner\Baseline\Cannot_Load_Baseline_Exception;
use Php_Unit\Runner\Baseline\Generator as BaselineGenerator;
use Php_Unit\Runner\Baseline\Reader;
use Php_Unit\Runner\Baseline\Writer;
use Php_Unit\Runner\Code_Coverage;
use Php_Unit\Runner\Code_Coverage_Initialization_Status;
use Php_Unit\Runner\Deprecation_Collector\Facade as DeprecationCollector;
use Php_Unit\Runner\Directory_Does_Not_Exist_Exception;
use Php_Unit\Runner\Error_Handler;
use Php_Unit\Runner\Extension\Extension_Bootstrapper;
use Php_Unit\Runner\Extension\Facade as ExtensionFacade;
use Php_Unit\Runner\Extension\Phar_Loader;
use Php_Unit\Runner\Garbage_Collection\Garbage_Collection_Handler;
use Php_Unit\Runner\Issue_Trigger_Resolver\Resolver;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use Php_Unit\Runner\Result_Cache\Default_Result_Cache;
use Php_Unit\Runner\Result_Cache\Null_Result_Cache;
use Php_Unit\Runner\Result_Cache\Result_Cache;
use Php_Unit\Runner\Result_Cache\Result_Cache_Handler;
use Php_Unit\Runner\Test_Suite_Sorter;
use Php_Unit\Runner\Version;
use Php_Unit\Test_Runner\Issue_Filter;
use Php_Unit\Test_Runner\Test_Result\Facade as TestResultFacade;
use Php_Unit\Text_Ui\Cli_Arguments\Builder;
use Php_Unit\Text_Ui\Cli_Arguments\Configuration as CliConfiguration;
use Php_Unit\Text_Ui\Cli_Arguments\Exception as ArgumentsException;
use Php_Unit\Text_Ui\Cli_Arguments\Xml_Configuration_File_Finder;
use Php_Unit\Text_Ui\Command\At_Least_Version_Command;
use Php_Unit\Text_Ui\Command\Check_Php_Configuration_Command;
use Php_Unit\Text_Ui\Command\Generate_Configuration_Command;
use Php_Unit\Text_Ui\Command\List_Groups_Command;
use Php_Unit\Text_Ui\Command\List_Test_Files_Command;
use Php_Unit\Text_Ui\Command\List_Tests_As_Text_Command;
use Php_Unit\Text_Ui\Command\List_Tests_As_Xml_Command;
use Php_Unit\Text_Ui\Command\List_Test_Suites_Command;
use Php_Unit\Text_Ui\Command\Migrate_Configuration_Command;
use Php_Unit\Text_Ui\Command\Result;
use Php_Unit\Text_Ui\Command\Show_Help_Command;
use Php_Unit\Text_Ui\Command\Show_Version_Command;
use Php_Unit\Text_Ui\Command\Version_Check_Command;
use Php_Unit\Text_Ui\Command\Warm_Code_Coverage_Cache_Command;
use Php_Unit\Text_Ui\Configuration\Bootstrap_Loader;
use Php_Unit\Text_Ui\Configuration\Bootstrap_Script_Does_Not_Exist_Exception;
use Php_Unit\Text_Ui\Configuration\Bootstrap_Script_Exception;
use Php_Unit\Text_Ui\Configuration\Code_Coverage_Filter_Registry;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Configuration\Php_Handler;
use Php_Unit\Text_Ui\Configuration\Registry;
use Php_Unit\Text_Ui\Configuration\Test_Suite_Builder;
use Php_Unit\Text_Ui\Output\Default_Printer;
use Php_Unit\Text_Ui\Output\Facade as OutputFacade;
use Php_Unit\Text_Ui\Output\Printer;
use Php_Unit\Text_Ui\Xml_Configuration\Configuration as XmlConfiguration;
use Php_Unit\Text_Ui\Xml_Configuration\Default_Configuration;
use Php_Unit\Text_Ui\Xml_Configuration\Loader;
use Php_Unit\Util\Http\Php_Downloader;
use function printf;
use function realpath;
use Sebastian_Bergmann\Timer\Timer;
use function sprintf;
use function str_contains;
use function str_starts_with;
use Throwable;
use function trim;
use function unlink;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Application
{
    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        $this->preload();
        try {
            Event_Facade::emitter()->application_started();
            $cli_configuration = $this->build_cli_configuration($argv);
            $path_to_xml_configuration_file = (new Xml_Configuration_File_Finder())->find($cli_configuration);
            $this->execute_commands_that_only_require_cli_configuration($cli_configuration, $path_to_xml_configuration_file);
            $xml_configuration = $this->load_xml_configuration($path_to_xml_configuration_file);
            $configuration = Registry::init($cli_configuration, $xml_configuration);
            (new Php_Handler())->handle($configuration->php());
            try {
                (new Bootstrap_Loader())->handle($configuration);
            } catch (Bootstrap_Script_Does_Not_Exist_Exception|Bootstrap_Script_Exception $e) {
                $this->exit_with_error_message($e->get_message());
            }
            $this->execute_commands_that_do_not_require_the_test_suite($configuration, $cli_configuration);
            $phar_extensions = null;
            $extension_requires_code_coverage_collection = false;
            $extension_replaces_output = false;
            $extension_replaces_progress_output = false;
            $extension_replaces_result_output = false;
            if (!$configuration->no_extensions()) {
                if ($configuration->has_phar_extension_directory()) {
                    $phar_extensions = (new Phar_Loader())->load_phar_extensions_in_directory($configuration->phar_extension_directory());
                }
                $bootstrapped_extensions = $this->bootstrap_extensions($configuration);
                $extension_requires_code_coverage_collection = $bootstrapped_extensions['requiresCodeCoverageCollection'];
                $extension_replaces_output = $bootstrapped_extensions['replacesOutput'];
                $extension_replaces_progress_output = $bootstrapped_extensions['replacesProgressOutput'];
                $extension_replaces_result_output = $bootstrapped_extensions['replacesResultOutput'];
            }
            $printer = Output_Facade::init($configuration, $extension_replaces_progress_output, $extension_replaces_result_output);
            if ($configuration->debug()) {
                Event_Facade::instance()->register_tracer(new Event_Logger('php://stdout', $configuration->with_telemetry()));
            }
            Test_Result_Facade::init();
            Deprecation_Collector::init();
            $this->register_logfile_writers($configuration);
            $test_dox_result_collector = $this->test_dox_result_collector($configuration);
            $result_cache = $this->initialize_test_result_cache($configuration);
            if ($configuration->control_garbage_collector()) {
                new Garbage_Collection_Handler(Event_Facade::instance(), $configuration->number_of_tests_before_garbage_collection());
            }
            $baseline_generator = $this->configure_baseline($configuration);
            Event_Facade::instance()->seal();
            Error_Handler::instance()->register_deprecation_handler();
            $test_suite = $this->build_test_suite($configuration);
            Error_Handler::instance()->restore_deprecation_handler();
            $this->execute_commands_that_require_the_test_suite($configuration, $cli_configuration, $test_suite);
            if ($test_suite->is_empty() && !$configuration->has_cli_arguments() && $configuration->test_suite()->is_empty()) {
                $this->execute(new Show_Help_Command(Result::FAILURE));
            }
            $coverage_initialization_status = Code_Coverage::instance()->init($configuration, Code_Coverage_Filter_Registry::instance(), $extension_requires_code_coverage_collection);
            if (!$configuration->debug() && !$extension_replaces_output) {
                $this->write_runtime_information($printer, $configuration);
                $this->write_phar_extension_information($printer, $phar_extensions);
                $this->write_random_seed_information($printer, $configuration);
                $printer->print(PHP_EOL);
            }
            $this->configure_deprecation_triggers($configuration);
            $this->configure_issue_trigger_resolvers($configuration);
            $timer = new Timer();
            $timer->start();
            if ($coverage_initialization_status === Code_Coverage_Initialization_Status::NOT_REQUESTED || $coverage_initialization_status === Code_Coverage_Initialization_Status::SUCCEEDED) {
                $runner = new Test_Runner();
                $runner->run($configuration, $result_cache, $test_suite);
            }
            $duration = $timer->stop();
            $test_dox_result = null;
            if (isset($test_dox_result_collector)) {
                $test_dox_result = $test_dox_result_collector->test_methods_grouped_by_class();
            }
            if ($test_dox_result !== null && $configuration->has_logfile_testdox_html()) {
                try {
                    Output_Facade::printer_for($configuration->logfile_testdox_html())->print((new Test_Dox_Html_Renderer())->render($test_dox_result));
                } catch (Directory_Does_Not_Exist_Exception|Invalid_Socket_Exception $e) {
                    Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot log test results in TestDox HTML format to "%s": %s', $configuration->logfile_testdox_html(), $e->get_message()));
                }
            }
            if ($test_dox_result !== null && $configuration->has_logfile_testdox_text()) {
                try {
                    Output_Facade::printer_for($configuration->logfile_testdox_text())->print((new Test_Dox_Text_Renderer())->render($test_dox_result));
                } catch (Directory_Does_Not_Exist_Exception|Invalid_Socket_Exception $e) {
                    Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot log test results in TestDox plain text format to "%s": %s', $configuration->logfile_testdox_text(), $e->get_message()));
                }
            }
            $result = Test_Result_Facade::result();
            if (!$extension_replaces_result_output && !$configuration->debug()) {
                Output_Facade::print_result($result, $test_dox_result, $duration, $configuration->has_specific_deprecation_to_stop_on());
            }
            Code_Coverage::instance()->generate_reports($printer, $configuration);
            if (isset($baseline_generator)) {
                (new Writer())->write($configuration->generate_baseline(), $baseline_generator->baseline());
                $printer->print(sprintf(PHP_EOL . 'Baseline written to %s.' . PHP_EOL, realpath($configuration->generate_baseline())));
            }
            $shell_exit_code = (new Shell_Exit_Code_Calculator())->calculate($configuration, $result);
            Event_Facade::emitter()->application_finished($shell_exit_code);
            return $shell_exit_code;
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            $this->exit_with_crash_message($t);
        }
        // @codeCoverageIgnoreEnd
    }
    private function execute(Command\Command $command, bool $requires_result_collected_from_events = false): never
    {
        $errored = false;
        if ($requires_result_collected_from_events) {
            try {
                Test_Result_Facade::init();
                Event_Facade::instance()->seal();
                $result_collected_from_events = Test_Result_Facade::result();
                $errored = $result_collected_from_events->has_test_triggered_phpunit_error_events();
            } catch (Event_Facade_Is_Sealed_Exception|Unknown_Subscriber_Type_Exception) {
            }
        }
        print Version::get_version_string() . PHP_EOL . PHP_EOL;
        if (!$errored) {
            $result = $command->execute();
            print $result->output();
            exit($result->shell_exit_code());
        }
        assert(isset($result_collected_from_events));
        print 'There were errors:' . PHP_EOL;
        foreach ($result_collected_from_events->test_triggered_phpunit_error_events() as $events) {
            foreach ($events as $event) {
                print PHP_EOL . trim($event->message()) . PHP_EOL;
            }
        }
        exit(Result::EXCEPTION);
    }
    /**
     * @param list<string> $argv
     */
    private function build_cli_configuration(array $argv): Cli_Configuration
    {
        try {
            $cli_configuration = (new Builder())->from_parameters($argv);
        } catch (Arguments_Exception $e) {
            $this->exit_with_error_message($e->get_message());
        }
        return $cli_configuration;
    }
    private function load_xml_configuration(false|string $configuration_file): Xml_Configuration
    {
        if ($configuration_file === false) {
            return Default_Configuration::create();
        }
        try {
            return (new Loader())->load($configuration_file);
        } catch (Throwable $e) {
            $this->exit_with_error_message($e->get_message());
        }
    }
    private function build_test_suite(Configuration $configuration): Test_Suite
    {
        try {
            return (new Test_Suite_Builder())->build($configuration);
        } catch (Exception $e) {
            $this->exit_with_error_message($e->get_message());
        }
    }
    /**
     * @return array{requiresCodeCoverageCollection: bool, replacesOutput: bool, replacesProgressOutput: bool, replacesResultOutput: bool}
     */
    private function bootstrap_extensions(Configuration $configuration): array
    {
        $facade = new Extension_Facade();
        $extension_bootstrapper = new Extension_Bootstrapper($configuration, $facade);
        foreach ($configuration->extension_bootstrappers() as $bootstrapper) {
            $extension_bootstrapper->bootstrap($bootstrapper['className'], $bootstrapper['parameters']);
        }
        return ['requiresCodeCoverageCollection' => $facade->requires_code_coverage_collection(), 'replacesOutput' => $facade->replaces_output(), 'replacesProgressOutput' => $facade->replaces_progress_output(), 'replacesResultOutput' => $facade->replaces_result_output()];
    }
    private function execute_commands_that_only_require_cli_configuration(Cli_Configuration $cli_configuration, false|string $configuration_file): void
    {
        if ($cli_configuration->generate_configuration()) {
            $this->execute(new Generate_Configuration_Command());
        }
        if ($cli_configuration->migrate_configuration()) {
            if ($configuration_file === false) {
                $this->exit_with_error_message('No configuration file found to migrate');
            }
            $this->execute(new Migrate_Configuration_Command(realpath($configuration_file)));
        }
        if ($cli_configuration->has_at_least_version()) {
            $this->execute(new At_Least_Version_Command($cli_configuration->at_least_version()));
        }
        if ($cli_configuration->version()) {
            $this->execute(new Show_Version_Command());
        }
        if ($cli_configuration->check_php_configuration()) {
            $this->execute(new Check_Php_Configuration_Command());
        }
        if ($cli_configuration->check_version()) {
            $this->execute(new Version_Check_Command(new Php_Downloader(), Version::major_version_number(), Version::id()));
        }
        if ($cli_configuration->help()) {
            $this->execute(new Show_Help_Command(Result::SUCCESS));
        }
    }
    private function execute_commands_that_do_not_require_the_test_suite(Configuration $configuration, Cli_Configuration $cli_configuration): void
    {
        if ($cli_configuration->warm_coverage_cache()) {
            $this->execute(new Warm_Code_Coverage_Cache_Command($configuration, Code_Coverage_Filter_Registry::instance()));
        }
    }
    private function execute_commands_that_require_the_test_suite(Configuration $configuration, Cli_Configuration $cli_configuration, Test_Suite $test_suite): void
    {
        if ($cli_configuration->list_suites()) {
            $this->execute(new List_Test_Suites_Command($test_suite));
        }
        if ($cli_configuration->list_groups()) {
            $this->execute(new List_Groups_Command($this->filtered_tests($configuration, $test_suite)), true);
        }
        if ($cli_configuration->list_tests()) {
            $this->execute(new List_Tests_As_Text_Command($this->filtered_tests($configuration, $test_suite)), true);
        }
        if ($cli_configuration->has_list_tests_xml()) {
            $this->execute(new List_Tests_As_Xml_Command($this->filtered_tests($configuration, $test_suite), $cli_configuration->list_tests_xml()), true);
        }
        if ($cli_configuration->list_test_files()) {
            $this->execute(new List_Test_Files_Command($this->filtered_tests($configuration, $test_suite)), true);
        }
    }
    private function write_runtime_information(Printer $printer, Configuration $configuration): void
    {
        $printer->print(Version::get_version_string() . PHP_EOL . PHP_EOL);
        $runtime = 'PHP ' . PHP_VERSION;
        if (Code_Coverage::instance()->is_active()) {
            $runtime .= ' with ' . Code_Coverage::instance()->driver_name_and_version();
        }
        $this->write_message($printer, 'Runtime', $runtime);
        if ($configuration->has_configuration_file()) {
            $this->write_message($printer, 'Configuration', $configuration->configuration_file());
        }
    }
    /**
     * @param ?list<string> $pharExtensions
     */
    private function write_phar_extension_information(Printer $printer, ?array $phar_extensions): void
    {
        if ($phar_extensions === null) {
            return;
        }
        foreach ($phar_extensions as $extension) {
            $this->write_message($printer, 'Extension', $extension);
        }
    }
    private function write_message(Printer $printer, string $type, string $message): void
    {
        $printer->print(sprintf("%-15s%s\n", $type . ':', $message));
    }
    private function write_random_seed_information(Printer $printer, Configuration $configuration): void
    {
        if ($configuration->execution_order() === Test_Suite_Sorter::ORDER_RANDOMIZED) {
            $this->write_message($printer, 'Random Seed', (string) $configuration->random_order_seed());
        }
    }
    private function register_logfile_writers(Configuration $configuration): void
    {
        if ($configuration->has_log_events_text()) {
            if (is_file($configuration->log_events_text())) {
                unlink($configuration->log_events_text());
            }
            Event_Facade::instance()->register_tracer(new Event_Logger($configuration->log_events_text(), $configuration->with_telemetry()));
        }
        if ($configuration->has_log_events_verbose_text()) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_deprecation('The "--log-events-verbose-text <file>" CLI option is deprecated and will be removed in PHPUnit 14. Use "--log-events-text <file> --with-telemetry" instead.');
            if (is_file($configuration->log_events_verbose_text())) {
                unlink($configuration->log_events_verbose_text());
            }
            Event_Facade::instance()->register_tracer(new Event_Logger($configuration->log_events_verbose_text(), true));
        }
        if ($configuration->has_logfile_junit()) {
            try {
                new Junit_Xml_Logger(Output_Facade::printer_for($configuration->logfile_junit()), Event_Facade::instance());
            } catch (Directory_Does_Not_Exist_Exception|Invalid_Socket_Exception $e) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot log test results in JUnit XML format to "%s": %s', $configuration->logfile_junit(), $e->get_message()));
            }
        }
        if ($configuration->has_logfile_otr()) {
            try {
                new Otr_Xml_Logger(Event_Facade::instance(), $configuration->logfile_otr(), $configuration->include_git_information_in_otr_logfile());
            } catch (Cannot_Open_Uri_For_Writing_Exception $e) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot log test results in Open Test Reporting XML format to "%s": %s', $configuration->logfile_otr(), $e->get_message()));
            }
        }
        if ($configuration->has_logfile_teamcity()) {
            try {
                new Team_City_Logger(Default_Printer::from($configuration->logfile_teamcity()), Event_Facade::instance());
            } catch (Directory_Does_Not_Exist_Exception|Invalid_Socket_Exception $e) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot log test results in TeamCity format to "%s": %s', $configuration->logfile_teamcity(), $e->get_message()));
            }
        }
    }
    private function test_dox_result_collector(Configuration $configuration): ?Test_Dox_Result_Collector
    {
        if ($configuration->has_logfile_testdox_html() || $configuration->has_logfile_testdox_text() || $configuration->output_is_test_dox()) {
            return new Test_Dox_Result_Collector(Event_Facade::instance(), new Issue_Filter($configuration->source()));
        }
        return null;
    }
    private function initialize_test_result_cache(Configuration $configuration): Result_Cache
    {
        if ($configuration->cache_result()) {
            $cache = new Default_Result_Cache($configuration->test_result_cache_file());
            new Result_Cache_Handler($cache, Event_Facade::instance());
            return $cache;
        }
        return new Null_Result_Cache();
    }
    private function configure_baseline(Configuration $configuration): ?Baseline_Generator
    {
        if ($configuration->has_generate_baseline()) {
            return new Baseline_Generator(Event_Facade::instance(), $configuration->source());
        }
        if ($configuration->source()->use_baseline()) {
            $baseline_file = $configuration->source()->baseline();
            $baseline = null;
            try {
                $baseline = (new Reader())->read($baseline_file);
            } catch (Cannot_Load_Baseline_Exception $e) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning($e->get_message());
            }
            if ($baseline !== null) {
                Error_Handler::instance()->use_baseline($baseline);
            }
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    private function exit_with_crash_message(Throwable $t): never
    {
        $message = $t->get_message();
        if (trim($message) === '') {
            $message = '(no message)';
        }
        printf('%s%sAn error occurred inside PHPUnit.%s%sMessage:  %s', PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, $message);
        $first = true;
        if ($t->get_previous() !== null) {
            $t = $t->get_previous();
        }
        do {
            printf('%s%s: %s:%d%s%s%s%s', PHP_EOL, $first ? 'Location' : 'Caused by', $t->get_file(), $t->get_line(), PHP_EOL, PHP_EOL, $t->get_trace_as_string(), PHP_EOL);
            $first = false;
        } while ($t = $t->get_previous());
        exit(Result::CRASH);
    }
    private function exit_with_error_message(string $message): never
    {
        print Version::get_version_string() . PHP_EOL . PHP_EOL . $message . PHP_EOL;
        exit(Result::EXCEPTION);
    }
    /**
     * @return list<PhptTestCase|TestCase>
     */
    private function filtered_tests(Configuration $configuration, Test_Suite $suite): array
    {
        (new Test_Suite_Filter_Processor())->process($configuration, $suite);
        return $suite->collect();
    }
    private function configure_deprecation_triggers(Configuration $configuration): void
    {
        $deprecation_triggers = ['functions' => [], 'methods' => []];
        foreach ($configuration->source()->deprecation_triggers()['functions'] as $function) {
            if (!function_exists($function)) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Function %s cannot be configured as a deprecation trigger because it is not declared', $function));
                continue;
            }
            $deprecation_triggers['functions'][] = $function;
        }
        foreach ($configuration->source()->deprecation_triggers()['methods'] as $method) {
            if (!str_contains($method, '::')) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('%s cannot be configured as a deprecation trigger because it is not in ClassName::methodName format', $method));
                continue;
            }
            [$class_name, $method_name] = explode('::', $method);
            if (!class_exists($class_name) || !method_exists($class_name, $method_name)) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Method %s::%s cannot be configured as a deprecation trigger because it is not declared', $class_name, $method_name));
                continue;
            }
            $deprecation_triggers['methods'][] = ['className' => $class_name, 'methodName' => $method_name];
        }
        if ($deprecation_triggers !== ['functions' => [], 'methods' => []]) {
            Error_Handler::instance()->use_deprecation_triggers($deprecation_triggers);
        }
    }
    private function configure_issue_trigger_resolvers(Configuration $configuration): void
    {
        $class_names = $configuration->source()->issue_trigger_resolvers();
        foreach (array_reverse($class_names) as $class_name) {
            if (!class_exists($class_name)) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Class %s cannot be used as an issue trigger resolver because it does not exist', $class_name));
                continue;
            }
            if (!in_array(Resolver::class, class_implements($class_name), true)) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Class %s cannot be used as an issue trigger resolver because it does not implement %s', $class_name, Resolver::class));
                continue;
            }
            Error_Handler::instance()->add_issue_trigger_resolver(new $class_name());
        }
    }
    private function preload(): void
    {
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            return;
        }
        $class_map_file = dirname((string) PHPUNIT_COMPOSER_INSTALL) . '/composer/autoload_classmap.php';
        if (!is_file($class_map_file)) {
            return;
        }
        foreach (require $class_map_file as $code_unit_name => $source_code_file) {
            if (!str_starts_with((string) $code_unit_name, 'PHPUnit\\') && !str_starts_with((string) $code_unit_name, 'SebastianBergmann\\')) {
                continue;
            }
            if (str_contains((string) $source_code_file, '/tests/')) {
                continue;
            }
            require_once $source_code_file;
        }
    }
}