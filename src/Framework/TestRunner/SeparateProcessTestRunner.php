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
namespace Php_Unit\Framework;

use function assert;
use function defined;
use function get_include_path;
use function hrtime;
use Php_Unit\Event\No_Previous_Throwable_Exception;
use Php_Unit\Runner\Code_Coverage;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use Php_Unit\Text_Ui\Configuration\Source_Mapper;
use Php_Unit\Util\Global_State;
use Php_Unit\Util\PHP\Job;
use Php_Unit\Util\PHP\Job_Runner_Registry;
use ReflectionClass;
use Sebastian_Bergmann\Template\InvalidArgumentException;
use Sebastian_Bergmann\Template\Template;
use function serialize;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function var_export;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Separate_Process_Test_Runner implements Isolated_Test_Runner
{
    private static ?string $source_map_file = null;
    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     */
    public function run(Test_Case $test, bool $preserve_global_state, bool $requires_xdebug): void
    {
        $class = new ReflectionClass($test);
        $bootstrap = '';
        $constants = '';
        $globals = '';
        $included_files = '';
        $ini_settings = '';
        if (Configuration_Registry::get()->has_bootstrap()) {
            $bootstrap = Configuration_Registry::get()->bootstrap();
        }
        if ($preserve_global_state) {
            $constants = Global_State::get_constants_as_string();
            $globals = Global_State::get_globals_as_string();
            $included_files = Global_State::get_included_files_as_string();
            $ini_settings = Global_State::get_ini_settings_as_string();
        }
        $coverage = Code_Coverage::instance()->is_active() ? 'true' : 'false';
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composer_autoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        } else {
            $composer_autoload = '\'\'';
        }
        if (defined('__PHPUNIT_PHAR__')) {
            $phar = var_export(__PHPUNIT_PHAR__, true);
        } else {
            $phar = '\'\'';
        }
        $data = var_export(serialize($test->provided_data()), true);
        $data_name = var_export($test->data_name(), true);
        $dependency_input = var_export(serialize($test->dependency_input()), true);
        $include_path = var_export(get_include_path(), true);
        // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
        // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
        $data = "'." . $data . ".'";
        $data_name = "'.(" . $data_name . ").'";
        $dependency_input = "'." . $dependency_input . ".'";
        $include_path = "'." . $include_path . ".'";
        $offset = hrtime();
        $serialized_configuration = $this->save_configuration_for_child_process();
        $process_result_file = $this->path_for_cached_source_map();
        $source_map_file = $this->source_map_file_for_child_process();
        $file = $class->get_file_name();
        assert($file !== false);
        $var = ['bootstrap' => $bootstrap, 'composerAutoload' => $composer_autoload, 'phar' => $phar, 'filename' => $file, 'className' => $class->get_name(), 'methodName' => $test->name(), 'collectCodeCoverageInformation' => $coverage, 'data' => $data, 'dataName' => $data_name, 'dependencyInput' => $dependency_input, 'constants' => $constants, 'globals' => $globals, 'include_path' => $include_path, 'included_files' => $included_files, 'iniSettings' => $ini_settings, 'name' => $test->name(), 'offsetSeconds' => (string) $offset[0], 'offsetNanoseconds' => (string) $offset[1], 'serializedConfiguration' => $serialized_configuration, 'processResultFile' => $process_result_file, 'sourceMapFile' => $source_map_file];
        $template = new Template(__DIR__ . '/templates/method.tpl');
        $template->set_var($var);
        $code = $template->render();
        assert($code !== '');
        Job_Runner_Registry::run_test_job(new Job($code, requiresXdebug: $requires_xdebug), $process_result_file, $test);
        @unlink($serialized_configuration);
    }
    private function source_map_file_for_child_process(): string
    {
        if (self::$source_map_file !== null) {
            return self::$source_map_file;
        }
        if (!Configuration_Registry::get()->source()->not_empty()) {
            self::$source_map_file = '';
            return self::$source_map_file;
        }
        $path = $this->path_for_cached_source_map();
        if ($path === false) {
            // @codeCoverageIgnoreStart
            self::$source_map_file = '';
            return self::$source_map_file;
            // @codeCoverageIgnoreEnd
        }
        if (!Source_Mapper::save_to($path, Configuration_Registry::get()->source())) {
            // @codeCoverageIgnoreStart
            self::$source_map_file = '';
            return self::$source_map_file;
            // @codeCoverageIgnoreEnd
        }
        self::$source_map_file = $path;
        return self::$source_map_file;
    }
    /**
     * @throws ProcessIsolationException
     */
    private function save_configuration_for_child_process(): string
    {
        $path = $this->path_for_cached_source_map();
        if ($path === false) {
            // @codeCoverageIgnoreStart
            throw new Process_Isolation_Exception();
            // @codeCoverageIgnoreEnd
        }
        if (!Configuration_Registry::save_to($path)) {
            // @codeCoverageIgnoreStart
            throw new Process_Isolation_Exception();
            // @codeCoverageIgnoreEnd
        }
        return $path;
    }
    private function path_for_cached_source_map(): false|string
    {
        return tempnam(sys_get_temp_dir(), 'phpunit_');
    }
}