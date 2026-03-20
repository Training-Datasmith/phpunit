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
namespace Php_Unit\Metadata\Api;

use function addcslashes;
use function array_column;
use function array_key_exists;
use function assert;
use function extension_loaded;
use function function_exists;
use function in_array;
use function ini_get;
use function method_exists;
use const PHP_OS;
use const PHP_OS_FAMILY;
use const PHP_VERSION;
use Php_Unit\Metadata\Parser\Registry;
use Php_Unit\Metadata\Requires_Environment_Variable;
use Php_Unit\Metadata\Requires_Function;
use Php_Unit\Metadata\Requires_Method;
use Php_Unit\Metadata\Requires_Operating_System;
use Php_Unit\Metadata\Requires_Operating_System_Family;
use Php_Unit\Metadata\Requires_Php;
use Php_Unit\Metadata\Requires_Php_Extension;
use Php_Unit\Metadata\Requires_Phpunit;
use Php_Unit\Metadata\Requires_Phpunit_Extension;
use Php_Unit\Metadata\Requires_Setting;
use Php_Unit\Runner\Version;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use function phpversion;
use function preg_match;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Requirements
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<string>
     */
    public function requirements_not_satisfied_for(string $class_name, string $method_name): array
    {
        $not_satisfied = [];
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name) as $metadata) {
            if ($metadata->is_requires_php()) {
                assert($metadata instanceof Requires_Php);
                if (!$metadata->version_requirement()->is_satisfied_by(PHP_VERSION)) {
                    $not_satisfied[] = sprintf('PHP %s is required.', $metadata->version_requirement()->as_string());
                }
            }
            if ($metadata->is_requires_php_extension()) {
                assert($metadata instanceof Requires_Php_Extension);
                $extension_version = phpversion($metadata->extension());
                if ($extension_version === false) {
                    $extension_version = '';
                }
                if (!extension_loaded($metadata->extension()) || $metadata->has_version_requirement() && !$metadata->version_requirement()->is_satisfied_by($extension_version)) {
                    $not_satisfied[] = sprintf('PHP extension %s%s is required.', $metadata->extension(), $metadata->has_version_requirement() ? ' ' . $metadata->version_requirement()->as_string() : '');
                }
            }
            if ($metadata->is_requires_phpunit()) {
                assert($metadata instanceof Requires_Phpunit);
                if (!$metadata->version_requirement()->is_satisfied_by(Version::id())) {
                    $not_satisfied[] = sprintf('PHPUnit %s is required.', $metadata->version_requirement()->as_string());
                }
            }
            if ($metadata->is_requires_phpunit_extension()) {
                assert($metadata instanceof Requires_Phpunit_Extension);
                $configuration = Configuration_Registry::get();
                $extension_bootstrappers = array_column($configuration->extension_bootstrappers(), 'className');
                if ($configuration->no_extensions() || !in_array($metadata->extension_class(), $extension_bootstrappers, true)) {
                    $not_satisfied[] = sprintf('PHPUnit extension "%s" is required.', $metadata->extension_class());
                }
            }
            if ($metadata->is_requires_environment_variable()) {
                assert($metadata instanceof Requires_Environment_Variable);
                if (!array_key_exists($metadata->environment_variable_name(), $_ENV) || $metadata->value() === null && $_ENV[$metadata->environment_variable_name()] === '') {
                    $not_satisfied[] = sprintf('Environment variable "%s" is required.', $metadata->environment_variable_name());
                    continue;
                }
                if ($metadata->value() !== null && $_ENV[$metadata->environment_variable_name()] !== $metadata->value()) {
                    $not_satisfied[] = sprintf('Environment variable "%s" is required to be "%s".', $metadata->environment_variable_name(), $metadata->value());
                }
            }
            if ($metadata->is_requires_operating_system_family()) {
                assert($metadata instanceof Requires_Operating_System_Family);
                if ($metadata->operating_system_family() !== PHP_OS_FAMILY) {
                    $not_satisfied[] = sprintf('Operating system %s is required.', $metadata->operating_system_family());
                }
            }
            if ($metadata->is_requires_operating_system()) {
                assert($metadata instanceof Requires_Operating_System);
                $pattern = sprintf('/%s/i', addcslashes($metadata->operating_system(), '/'));
                if (preg_match($pattern, PHP_OS) === 0) {
                    $not_satisfied[] = sprintf('Operating system %s is required.', $metadata->operating_system());
                }
            }
            if ($metadata->is_requires_function()) {
                assert($metadata instanceof Requires_Function);
                if (!function_exists($metadata->function_name())) {
                    $not_satisfied[] = sprintf('Function %s() is required.', $metadata->function_name());
                }
            }
            if ($metadata->is_requires_method()) {
                assert($metadata instanceof Requires_Method);
                if (!method_exists($metadata->class_name(), $metadata->method_name())) {
                    $not_satisfied[] = sprintf('Method %s::%s() is required.', $metadata->class_name(), $metadata->method_name());
                }
            }
            if ($metadata->is_requires_setting()) {
                assert($metadata instanceof Requires_Setting);
                if (ini_get($metadata->setting()) !== $metadata->value()) {
                    $not_satisfied[] = sprintf('Setting "%s" is required to be "%s".', $metadata->setting(), $metadata->value());
                }
            }
        }
        return $not_satisfied;
    }
    public function requires_xdebug(string $class_name, string $method_name): bool
    {
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name) as $metadata) {
            if ($metadata->is_requires_php_extension()) {
                if ($metadata->extension() === 'xdebug') {
                    return true;
                }
            }
        }
        return false;
    }
}