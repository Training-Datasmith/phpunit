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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use function in_array;
use function is_dir;
use function is_file;
use const PHP_VERSION;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Exception as FrameworkException;
use Php_Unit\Framework\Test_Suite as TestSuiteObject;
use Php_Unit\Text_Ui\Configuration\Test_Suite_Collection;
use Php_Unit\Text_Ui\RuntimeException;
use Php_Unit\Text_Ui\Test_Directory_Not_Found_Exception;
use Php_Unit\Text_Ui\Test_File_Not_Found_Exception;
use Sebastian_Bergmann\File_Iterator\Facade;
use function sprintf;
use function str_contains;
use function version_compare;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Suite_Mapper
{
    /**
     * @param non-empty-string       $xmlConfigurationFile
     * @param list<non-empty-string> $includeTestSuites
     * @param list<non-empty-string> $excludeTestSuites
     *
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function map(string $xml_configuration_file, Test_Suite_Collection $configured_test_suites, array $include_test_suites, array $exclude_test_suites): Test_Suite_Object
    {
        try {
            $result = Test_Suite_Object::empty($xml_configuration_file);
            $processed = [];
            foreach ($configured_test_suites as $configured_test_suite) {
                if ($include_test_suites !== [] && !in_array($configured_test_suite->name(), $include_test_suites, true)) {
                    continue;
                }
                if ($exclude_test_suites !== [] && in_array($configured_test_suite->name(), $exclude_test_suites, true)) {
                    continue;
                }
                $test_suite_name = $configured_test_suite->name();
                $exclude = [];
                foreach ($configured_test_suite->exclude()->as_array() as $file) {
                    $exclude[] = $file->path();
                }
                $test_suite = Test_Suite_Object::empty($configured_test_suite->name());
                $empty = true;
                foreach ($configured_test_suite->directories() as $directory) {
                    if (!str_contains($directory->path(), '*') && !is_dir($directory->path())) {
                        throw new Test_Directory_Not_Found_Exception($directory->path());
                    }
                    if (!version_compare(PHP_VERSION, $directory->php_version(), $directory->php_version_operator()->as_string())) {
                        continue;
                    }
                    $files = (new Facade())->get_files_as_array($directory->path(), $directory->suffix(), $directory->prefix(), $exclude);
                    $groups = $directory->groups();
                    foreach ($files as $file) {
                        if (isset($processed[$file])) {
                            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot add file %s to test suite "%s" as it was already added to test suite "%s"', $file, $test_suite_name, $processed[$file]));
                            continue;
                        }
                        $processed[$file] = $test_suite_name;
                        $empty = false;
                        $test_suite->add_test_file($file, $groups);
                    }
                }
                foreach ($configured_test_suite->files() as $file) {
                    if (!is_file($file->path())) {
                        throw new Test_File_Not_Found_Exception($file->path());
                    }
                    if (!version_compare(PHP_VERSION, $file->php_version(), $file->php_version_operator()->as_string())) {
                        continue;
                    }
                    if (isset($processed[$file->path()])) {
                        Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot add file %s to test suite "%s" as it was already added to test suite "%s"', $file->path(), $test_suite_name, $processed[$file->path()]));
                        continue;
                    }
                    $processed[$file->path()] = $test_suite_name;
                    $empty = false;
                    $test_suite->add_test_file($file->path(), $file->groups());
                }
                if (!$empty) {
                    $result->add_test($test_suite);
                }
            }
            return $result;
        } catch (Framework_Exception $e) {
            throw new RuntimeException($e->get_message(), $e->get_code(), $e);
        }
    }
}