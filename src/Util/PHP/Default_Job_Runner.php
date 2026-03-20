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
namespace Php_Unit\Util\PHP;

use function array_any;
use function array_keys;
use function array_merge;
use function array_values;
use function assert;
use function fclose;
use function file_put_contents;
use function function_exists;
use function fwrite;
use function ini_get_all;
use function is_array;
use function is_resource;
use const PHP_BINARY;
use const PHP_SAPI;
use Php_Unit\Event\Facade;
use Php_Unit\Runner\Code_Coverage;
use function proc_close;
use function proc_open;
use Sebastian_Bergmann\Environment\Runtime;
use function str_starts_with;
use function stream_get_contents;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;
use function xdebug_is_debugger_active;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Default_Job_Runner extends Job_Runner
{
    /**
     * @throws PhpProcessException
     */
    public function run(Job $job): Result
    {
        $temporary_file = null;
        if ($job->has_input()) {
            $temporary_file = tempnam(sys_get_temp_dir(), 'phpunit_');
            if ($temporary_file === false || file_put_contents($temporary_file, $job->code()) === false) {
                // @codeCoverageIgnoreStart
                throw new Php_Process_Exception('Unable to write temporary file');
                // @codeCoverageIgnoreEnd
            }
            $job = new Job($job->input(), $job->php_settings(), $job->environment_variables(), $job->arguments(), null, $job->redirect_errors(), $job->requires_xdebug());
        }
        assert($temporary_file !== '');
        return $this->run_process($job, $temporary_file);
    }
    /**
     * @param ?non-empty-string $temporaryFile
     *
     * @throws PhpProcessException
     */
    private function run_process(Job $job, ?string $temporary_file): Result
    {
        $environment_variables = null;
        if ($job->has_environment_variables()) {
            /** @phpstan-ignore nullCoalesce.variable */
            $environment_variables = $_SERVER ?? [];
            unset($environment_variables['argv'], $environment_variables['argc']);
            $environment_variables = array_merge($environment_variables, $job->environment_variables());
            foreach ($environment_variables as $key => $value) {
                if (is_array($value)) {
                    unset($environment_variables[$key]);
                }
            }
            unset($key, $value);
        }
        $pipe_spec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        if ($job->redirect_errors()) {
            $pipe_spec[2] = ['redirect', 1];
        }
        $process = proc_open($this->build_command($job, $temporary_file), $pipe_spec, $pipes, null, $environment_variables);
        if (!is_resource($process)) {
            // @codeCoverageIgnoreStart
            throw new Php_Process_Exception('Unable to spawn worker process');
            // @codeCoverageIgnoreEnd
        }
        Facade::emitter()->child_process_started();
        fwrite($pipes[0], $job->code());
        fclose($pipes[0]);
        $stdout = '';
        $stderr = '';
        if (isset($pipes[1])) {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        }
        if (isset($pipes[2])) {
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
        }
        proc_close($process);
        if ($temporary_file !== null) {
            unlink($temporary_file);
        }
        assert($stdout !== false);
        assert($stderr !== false);
        return new Result($stdout, $stderr);
    }
    /**
     * @return non-empty-list<string>
     */
    private function build_command(Job $job, ?string $file): array
    {
        $runtime = new Runtime();
        $command = [PHP_BINARY];
        $php_settings = $job->php_settings();
        $xdebug_mode_configured_explicitly = array_any($php_settings, static fn(string $php_setting): bool => str_starts_with($php_setting, 'xdebug.mode'));
        if ($runtime->has_pcov()) {
            $pcov_settings = ini_get_all('pcov');
            assert($pcov_settings !== false);
            $php_settings = array_merge($php_settings, $runtime->get_current_settings(array_keys($pcov_settings)));
        } elseif ($runtime->has_xdebug()) {
            assert(function_exists('xdebug_is_debugger_active'));
            $xdebug_settings = ini_get_all('xdebug');
            assert($xdebug_settings !== false);
            $php_settings = array_merge($php_settings, $runtime->get_current_settings(array_keys($xdebug_settings)));
            if (!$xdebug_mode_configured_explicitly && !Code_Coverage::instance()->is_active() && xdebug_is_debugger_active() === false && !$job->requires_xdebug()) {
                // disable xdebug to speedup test execution
                $php_settings['xdebug.mode'] = 'xdebug.mode=off';
            }
        }
        $command = array_merge($command, $this->settings_to_parameters(array_values($php_settings)));
        if (PHP_SAPI === 'phpdbg') {
            $command[] = '-qrr';
            if ($file === null) {
                $command[] = 's=';
            }
        }
        if ($file !== null) {
            $command[] = '-f';
            $command[] = $file;
        }
        if ($job->has_arguments()) {
            if ($file === null) {
                $command[] = '--';
            }
            foreach ($job->arguments() as $argument) {
                $command[] = trim($argument);
            }
        }
        return $command;
    }
    /**
     * @param list<string> $settings
     *
     * @return list<string>
     */
    private function settings_to_parameters(array $settings): array
    {
        $buffer = [];
        foreach ($settings as $setting) {
            $buffer[] = '-d';
            $buffer[] = $setting;
        }
        return $buffer;
    }
}