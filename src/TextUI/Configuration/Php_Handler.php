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

use function constant;
use function define;
use function defined;
use function getenv;
use function implode;
use function ini_get;
use function ini_set;
use const PATH_SEPARATOR;
use Php_Unit\Event\Facade as EventFacade;
use function putenv;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Php_Handler
{
    public function handle(Php $configuration): void
    {
        $this->handle_include_paths($configuration->include_paths());
        $this->handle_ini_settings($configuration->ini_settings());
        $this->handle_constants($configuration->constants());
        $this->handle_global_variables($configuration->global_variables());
        $this->handle_server_variables($configuration->server_variables());
        $this->handle_env_variables($configuration->env_variables());
        $this->handle_variables('_POST', $configuration->post_variables());
        $this->handle_variables('_GET', $configuration->get_variables());
        $this->handle_variables('_COOKIE', $configuration->cookie_variables());
        $this->handle_variables('_FILES', $configuration->files_variables());
        $this->handle_variables('_REQUEST', $configuration->request_variables());
    }
    private function handle_include_paths(Directory_Collection $include_paths): void
    {
        if (!$include_paths->is_empty()) {
            $include_paths_as_strings = [];
            foreach ($include_paths as $include_path) {
                $include_paths_as_strings[] = $include_path->path();
            }
            ini_set('include_path', implode(PATH_SEPARATOR, $include_paths_as_strings) . PATH_SEPARATOR . ini_get('include_path'));
        }
    }
    private function handle_ini_settings(Ini_Setting_Collection $ini_settings): void
    {
        foreach ($ini_settings as $ini_setting) {
            $value = $ini_setting->value();
            if (defined($value)) {
                $value = (string) constant($value);
            }
            $error = '';
            set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) use (&$error): true {
                $error = $errstr;
                return true;
            });
            $success = ini_set($ini_setting->name(), $value);
            restore_error_handler();
            if ($success === false) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Failed to set "%s=%s": %s', $ini_setting->name(), $value, $error));
            }
        }
    }
    private function handle_constants(Constant_Collection $constants): void
    {
        foreach ($constants as $constant) {
            if (!defined($constant->name())) {
                define($constant->name(), $constant->value());
            }
        }
    }
    private function handle_global_variables(Variable_Collection $variables): void
    {
        foreach ($variables as $variable) {
            $GLOBALS[$variable->name()] = $variable->value();
        }
    }
    private function handle_server_variables(Variable_Collection $variables): void
    {
        foreach ($variables as $variable) {
            $_SERVER[$variable->name()] = $variable->value();
        }
    }
    private function handle_variables(string $target, Variable_Collection $variables): void
    {
        foreach ($variables as $variable) {
            $GLOBALS[$target][$variable->name()] = $variable->value();
        }
    }
    private function handle_env_variables(Variable_Collection $variables): void
    {
        foreach ($variables as $variable) {
            $name = $variable->name();
            $value = $variable->value();
            $force = $variable->force();
            if ($force || getenv($name) === false) {
                putenv("{$name}={$value}");
            }
            $value = getenv($name);
            if ($force || !isset($_ENV[$name])) {
                $_ENV[$name] = $value;
            }
        }
    }
}