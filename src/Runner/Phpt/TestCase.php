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
namespace Php_Unit\Runner\Phpt;

use function array_merge;
use function basename;
use function debug_backtrace;
use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function explode;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function is_array;
use function is_file;
use function ltrim;
use function ob_get_clean;
use function ob_start;
use Php_Unit\Event\Code\Phpt;
use Php_Unit\Event\Code\Throwable_Builder;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Event\No_Previous_Throwable_Exception;
use Php_Unit\Framework\Assert;
use Php_Unit\Framework\Assertion_Failed_Error;
use Php_Unit\Framework\Execution_Order_Dependency;
use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Incomplete_Test_Error;
use Php_Unit\Framework\Phpt_Assertion_Failed_Error;
use Php_Unit\Framework\Reorderable;
use Php_Unit\Framework\Self_Describing;
use Php_Unit\Framework\Test;
use Php_Unit\Runner\Code_Coverage;
use Php_Unit\Runner\Code_Coverage_File_Exists_Exception;
use Php_Unit\Runner\Exception;
use Php_Unit\Util\PHP\Job;
use Php_Unit\Util\PHP\Job_Runner_Registry;
use function preg_match;
use function preg_replace;
use function preg_split;
use function realpath;
use Sebastian_Bergmann\Code_Coverage\Data\Raw_Code_Coverage_Data;
use Sebastian_Bergmann\Code_Coverage\InvalidArgumentException;
use Sebastian_Bergmann\Code_Coverage\Reflection_Exception;
use Sebastian_Bergmann\Code_Coverage\Test\Test_Size;
use Sebastian_Bergmann\Code_Coverage\Test\Test_Status;
use Sebastian_Bergmann\Code_Coverage\Test_Id_Missing_Exception;
use Sebastian_Bergmann\Code_Coverage\Unintentionally_Covered_Code_Exception;
use function sprintf;
use staabm\Side_Effects_Detector\Side_Effect;
use staabm\Side_Effects_Detector\Side_Effects_Detector;
use function str_contains;
use function str_starts_with;
use function strncasecmp;
use function substr;
use Throwable;
use function trim;
use function unlink;
use function unserialize;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see https://qa.php.net/phpt_details.php
 */
final readonly class Test_Case implements Reorderable, Self_Describing, Test
{
    /**
     * @param non-empty-string $filename
     */
    public function __construct(private string $filename)
    {
        $this->ensure_coverage_file_does_not_exist();
    }
    public function count(): int
    {
        return 1;
    }
    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\Template\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ReflectionException
     * @throws TestIdMissingException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(): void
    {
        $emitter = Event_Facade::emitter();
        $parser = new Parser();
        $emitter->test_preparation_started($this->value_object_for_events());
        try {
            $sections = $parser->parse($this->filename);
        } catch (Exception $e) {
            $emitter->test_prepared($this->value_object_for_events());
            $emitter->test_errored($this->value_object_for_events(), Throwable_Builder::from($e));
            $emitter->test_finished($this->value_object_for_events(), 0);
            return;
        }
        $code = (new Renderer())->render($this->filename, $sections['FILE']);
        $xfail = false;
        $environment_variables = [];
        $php_settings = $parser->parse_ini_section($this->settings(Code_Coverage::instance()->is_active()));
        $input = null;
        $arguments = [];
        $emitter->test_prepared($this->value_object_for_events());
        if (isset($sections['INI'])) {
            $php_settings = $parser->parse_ini_section($sections['INI'], $php_settings);
        }
        if (isset($sections['ENV'])) {
            $environment_variables = $parser->parse_env_section($sections['ENV']);
        }
        if ($this->should_test_be_skipped($sections, $php_settings)) {
            return;
        }
        if (isset($sections['XFAIL'])) {
            $xfail = trim($sections['XFAIL']);
        }
        if (isset($sections['STDIN'])) {
            $input = $sections['STDIN'];
        }
        if (isset($sections['ARGS'])) {
            $arguments = explode(' ', $sections['ARGS']);
        }
        if (Code_Coverage::instance()->is_active()) {
            $code_coverage_cache_directory = null;
            if (Code_Coverage::instance()->code_coverage()->caches_static_analysis()) {
                $code_coverage_cache_directory = Code_Coverage::instance()->code_coverage()->cache_directory();
            }
            (new Renderer())->render_for_coverage($code, Code_Coverage::instance()->code_coverage()->collects_branch_and_path_coverage(), $code_coverage_cache_directory, $this->coverage_files());
        }
        $job_result = Job_Runner_Registry::run(new Job($code, $this->stringify_ini($php_settings), $environment_variables, $arguments, $input, true));
        Event_Facade::emitter()->child_process_finished($job_result->stdout(), $job_result->stderr());
        $output = $job_result->stdout();
        if (Code_Coverage::instance()->is_active()) {
            $coverage = $this->cleanup_for_coverage();
            Code_Coverage::instance()->code_coverage()->start($this->filename, Test_Size::Large);
            Code_Coverage::instance()->code_coverage()->append($coverage, $this->filename, true, Test_Status::Unknown);
        }
        $passed = true;
        try {
            $this->assert_phpt_expectation($sections, $output);
        } catch (Assertion_Failed_Error $e) {
            $failure = $e;
            if ($xfail !== false) {
                $failure = new Incomplete_Test_Error($xfail, 0, $e);
            } elseif ($e instanceof Expectation_Failed_Exception) {
                $comparison_failure = $e->get_comparison_failure();
                if ($comparison_failure !== null) {
                    $diff = $comparison_failure->get_diff();
                } else {
                    $diff = $e->get_message();
                }
                $hint = $this->location_hint_from_diff($diff, $sections);
                $trace = array_merge($hint, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
                $failure = new Phpt_Assertion_Failed_Error($e->get_message(), 0, (string) $trace[0]['file'], (int) $trace[0]['line'], $trace, $comparison_failure !== null ? $diff : '');
            }
            if ($failure instanceof Incomplete_Test_Error) {
                $emitter->test_marked_as_incomplete($this->value_object_for_events(), Throwable_Builder::from($failure));
            } else {
                $emitter->test_failed($this->value_object_for_events(), Throwable_Builder::from($failure), null);
            }
            $passed = false;
        } catch (Throwable $t) {
            $emitter->test_errored($this->value_object_for_events(), Throwable_Builder::from($t));
            $passed = false;
        }
        if ($passed) {
            $emitter->test_passed($this->value_object_for_events());
        }
        $this->run_clean($sections, Code_Coverage::instance()->is_active());
        $emitter->test_finished($this->value_object_for_events(), 1);
    }
    /**
     * Returns the name of the test case.
     */
    public function get_name(): string
    {
        return $this->to_string();
    }
    /**
     * Returns a string representation of the test case.
     */
    public function to_string(): string
    {
        return $this->filename;
    }
    public function sort_id(): string
    {
        return $this->filename;
    }
    /**
     * @return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        return [];
    }
    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        return [];
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function value_object_for_events(): Phpt
    {
        return new Phpt($this->filename);
    }
    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    private function assert_phpt_expectation(array $sections, string $output): void
    {
        $assertions = ['EXPECT' => 'assertEquals', 'EXPECTF' => 'assertStringMatchesFormat', 'EXPECTREGEX' => 'assertMatchesRegularExpression'];
        $actual = preg_replace('/\r\n/', "\n", trim($output));
        foreach ($assertions as $section_name => $section_assertion) {
            if (isset($sections[$section_name])) {
                $section_content = preg_replace('/\r\n/', "\n", trim($sections[$section_name]));
                $expected = $section_name === 'EXPECTREGEX' ? "/{$section_content}/" : $section_content;
                /** @phpstan-ignore staticMethod.dynamicName */
                Assert::$section_assertion($expected, $actual);
                return;
            }
        }
        throw new Invalid_Phpt_File_Exception();
    }
    /**
     * @param array<non-empty-string, non-empty-string>                         $sections
     * @param array<non-empty-string, array<non-empty-string>|non-empty-string> $settings
     */
    private function should_test_be_skipped(array $sections, array $settings): bool
    {
        if (!isset($sections['SKIPIF'])) {
            return false;
        }
        $skip_if_code = (new Renderer())->render($this->filename, $sections['SKIPIF']);
        if ($this->should_run_in_subprocess($sections, $skip_if_code)) {
            $job_result = Job_Runner_Registry::run(new Job($skip_if_code, $this->stringify_ini($settings)));
            $output = $job_result->stdout();
            Event_Facade::emitter()->child_process_finished($output, $job_result->stderr());
        } else {
            $output = $this->run_code_in_local_sandbox($skip_if_code);
        }
        $this->trigger_runner_warning_on_php_errors('SKIPIF', $output);
        if (strncasecmp('skip', ltrim($output), 4) === 0) {
            $message = '';
            if (preg_match('/^\s*skip\s*(.+)\s*/i', $output, $skip_match)) {
                $message = substr($skip_match[1], 2);
            }
            Event_Facade::emitter()->test_skipped($this->value_object_for_events(), $message);
            Event_Facade::emitter()->test_finished($this->value_object_for_events(), 0);
            return true;
        }
        return false;
    }
    /**
     * @param array<non-empty-string, non-empty-string> $sections
     */
    private function should_run_in_subprocess(array $sections, string $clean_code): bool
    {
        if (isset($sections['INI'])) {
            // to get per-test INI settings, we need a dedicated subprocess
            return true;
        }
        $detector = new Side_Effects_Detector();
        $side_effects = $detector->get_side_effects($clean_code);
        if ($side_effects === []) {
            // no side-effects
            return false;
        }
        foreach ($side_effects as $side_effect) {
            if ($side_effect === Side_Effect::STANDARD_OUTPUT) {
                // stdout is fine, we will catch it using output-buffering
                continue;
            }
            if ($side_effect === Side_Effect::INPUT_OUTPUT) {
                // IO is fine, as it doesn't pollute the main process
                continue;
            }
            return true;
        }
        return false;
    }
    private function run_code_in_local_sandbox(string $code): string
    {
        $code = preg_replace('/^<\?(?:php)?|\?>\s*+$/', '', $code);
        $code = preg_replace('/declare\S?\([^)]+\)\S?;/', '', (string) $code);
        // wrap in immediately invoked function to isolate local-side-effects of $code from our own process
        $code = '(function() {' . $code . '})();';
        ob_start();
        @eval($code);
        return ob_get_clean();
    }
    /**
     * @param array<non-empty-string, non-empty-string> $sections
     */
    private function run_clean(array $sections, bool $collect_coverage): void
    {
        if (!isset($sections['CLEAN'])) {
            return;
        }
        $clean_code = (new Renderer())->render($this->filename, $sections['CLEAN']);
        if ($this->should_run_in_subprocess($sections, $clean_code)) {
            $job_result = Job_Runner_Registry::run(new Job($clean_code, $this->settings($collect_coverage)));
            $output = $job_result->stdout();
            Event_Facade::emitter()->child_process_finished($job_result->stdout(), $job_result->stderr());
        } else {
            $output = $this->run_code_in_local_sandbox($clean_code);
        }
        $this->trigger_runner_warning_on_php_errors('CLEAN', $output);
    }
    /**
     * @phpstan-ignore return.internalClass
     */
    private function cleanup_for_coverage(): Raw_Code_Coverage_Data
    {
        /**
         * @phpstan-ignore staticMethod.internalClass
         */
        $coverage = Raw_Code_Coverage_Data::from_xdebug_without_path_coverage([]);
        $files = $this->coverage_files();
        $buffer = false;
        if (is_file($files['coverage'])) {
            $buffer = @file_get_contents($files['coverage']);
        }
        if ($buffer !== false) {
            $coverage = @unserialize($buffer, ['allowed_classes' => [
                /** @phpstan-ignore classConstant.internalClass */
                Raw_Code_Coverage_Data::class,
            ]]);
            if ($coverage === false) {
                /**
                 * @phpstan-ignore staticMethod.internalClass
                 */
                $coverage = Raw_Code_Coverage_Data::from_xdebug_without_path_coverage([]);
            }
        }
        foreach ($files as $file) {
            @unlink($file);
        }
        return $coverage;
    }
    /**
     * @return array{coverage: non-empty-string, job: non-empty-string}
     */
    private function coverage_files(): array
    {
        $base_dir = dirname(realpath($this->filename)) . DIRECTORY_SEPARATOR;
        $basename = basename($this->filename, 'phpt');
        return ['coverage' => $base_dir . $basename . 'coverage', 'job' => $base_dir . $basename . 'php'];
    }
    /**
     * @param array<non-empty-string, array<non-empty-string>|non-empty-string> $ini
     *
     * @return list<non-empty-string>
     */
    private function stringify_ini(array $ini): array
    {
        $settings = [];
        foreach ($ini as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $settings[] = $key . '=' . $val;
                }
                continue;
            }
            $settings[] = $key . '=' . $value;
        }
        return $settings;
    }
    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @return non-empty-list<array{file: non-empty-string, line: int}>
     */
    private function location_hint_from_diff(string $message, array $sections): array
    {
        $needle = '';
        $previous_line = '';
        $block = 'message';
        foreach (preg_split('/\r\n|\r|\n/', $message) as $line) {
            $line = trim($line);
            if ($block === 'message' && $line === '--- Expected') {
                $block = 'expected';
            }
            if ($block === 'expected' && $line === '@@ @@') {
                $block = 'diff';
            }
            if ($block === 'diff') {
                if (str_starts_with($line, '+')) {
                    $needle = $this->clean_diff_line($previous_line);
                    break;
                }
                if (str_starts_with($line, '-')) {
                    $needle = $this->clean_diff_line($line);
                    break;
                }
            }
            if ($line !== '') {
                $previous_line = $line;
            }
        }
        return $this->location_hint($needle, $sections);
    }
    private function clean_diff_line(string $line): string
    {
        if (preg_match('/^[\-+]([\'\"]?)(.*)\1$/', $line, $matches)) {
            return $matches[2];
        }
        return $line;
    }
    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @return non-empty-list<array{file: non-empty-string, line: int}>
     */
    private function location_hint(string $needle, array $sections): array
    {
        $needle = trim($needle);
        if ($needle === '') {
            return [['file' => realpath($this->filename), 'line' => 1]];
        }
        $search = [
            // 'FILE',
            'EXPECT',
            'EXPECTF',
            'EXPECTREGEX',
        ];
        foreach ($search as $section) {
            if (!isset($sections[$section])) {
                continue;
            }
            if (isset($sections[$section . '_EXTERNAL'])) {
                $external_file = trim($sections[$section . '_EXTERNAL']);
                return [['file' => realpath(dirname($this->filename) . DIRECTORY_SEPARATOR . $external_file), 'line' => 1], ['file' => realpath($this->filename), 'line' => ($sections[$section . '_EXTERNAL_offset'] ?? 0) + 1]];
            }
            $section_offset = $sections[$section . '_offset'] ?? 0;
            $offset = $section_offset + 1;
            foreach (preg_split('/\r\n|\r|\n/', $sections[$section]) as $line) {
                if (str_contains($line, $needle)) {
                    return [['file' => realpath($this->filename), 'line' => $offset]];
                }
                $offset++;
            }
        }
        return [['file' => realpath($this->filename), 'line' => 1]];
    }
    /**
     * @return list<string>
     */
    private function settings(bool $collect_coverage): array
    {
        $settings = ['allow_url_fopen=1', 'auto_append_file=', 'auto_prepend_file=', 'disable_functions=', 'display_errors=1', 'docref_ext=.html', 'docref_root=', 'error_append_string=', 'error_prepend_string=', 'error_reporting=-1', 'html_errors=0', 'log_errors=0', 'open_basedir=', 'output_buffering=Off', 'output_handler=', 'report_zend_debug=0'];
        if (extension_loaded('pcov')) {
            if ($collect_coverage) {
                $settings[] = 'pcov.enabled=1';
            } else {
                $settings[] = 'pcov.enabled=0';
            }
        }
        if (extension_loaded('xdebug')) {
            if ($collect_coverage) {
                $settings[] = 'xdebug.mode=coverage';
            }
        }
        return $settings;
    }
    private function trigger_runner_warning_on_php_errors(string $section, string $output): void
    {
        if (str_contains($output, 'Parse error:')) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('%s section triggered a parse error: %s', $section, $output));
        }
        if (str_contains($output, 'Fatal error:')) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('%s section triggered a fatal error: %s', $section, $output));
        }
    }
    /**
     * @throws CodeCoverageFileExistsException
     */
    private function ensure_coverage_file_does_not_exist(): void
    {
        $files = $this->coverage_files();
        if (file_exists($files['coverage'])) {
            throw new Code_Coverage_File_Exists_Exception(sprintf('File %s exists, PHPT test %s will not be executed', $files['coverage'], $this->filename));
        }
    }
}