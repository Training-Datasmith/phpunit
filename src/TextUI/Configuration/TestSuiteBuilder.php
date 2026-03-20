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

use function assert;
use function count;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function file;
use function is_dir;
use function is_file;
use const PHP_EOL;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Exception;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Test_Suite_Loader;
use Php_Unit\Text_Ui\RuntimeException;
use Php_Unit\Text_Ui\Test_Directory_Not_Found_Exception;
use Php_Unit\Text_Ui\Test_File_Not_Found_Exception;
use Php_Unit\Text_Ui\Xml_Configuration\Test_Suite_Mapper;
use function realpath;
use Sebastian_Bergmann\File_Iterator\Facade as FileIteratorFacade;
use function str_ends_with;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Suite_Builder
{
    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function build(Configuration $configuration): Test_Suite
    {
        if ($configuration->has_cli_arguments() || $configuration->has_test_files_file()) {
            $arguments = [];
            if ($configuration->has_cli_arguments()) {
                foreach ($configuration->cli_arguments() as $cli_argument) {
                    $argument = realpath($cli_argument);
                    if (!$argument) {
                        throw new Test_File_Not_Found_Exception($cli_argument);
                    }
                    $arguments[] = $argument;
                }
            }
            if ($configuration->has_test_files_file()) {
                if (!is_file($configuration->test_files_file())) {
                    throw new RuntimeException('Cannot read from ' . $configuration->test_files_file());
                }
                $directory = dirname($configuration->test_files_file()) . DIRECTORY_SEPARATOR;
                foreach (file($configuration->test_files_file()) as $file) {
                    $file = trim($file);
                    $argument = realpath($file);
                    if (!$argument) {
                        $argument = realpath($directory . $file);
                    }
                    if (!$argument) {
                        throw new Test_File_Not_Found_Exception($file);
                    }
                    $arguments[] = $argument;
                }
            }
            if (count($arguments) === 1) {
                $test_suite = $this->test_suite_from_path($arguments[0], $configuration->test_suffixes());
            } else {
                $test_suite = $this->test_suite_from_path_list($arguments, $configuration->test_suffixes());
            }
        }
        if (!isset($test_suite)) {
            $xml_configuration_file = $configuration->has_configuration_file() ? $configuration->configuration_file() : 'Root Test Suite';
            assert($xml_configuration_file !== '');
            $test_suite = (new Test_Suite_Mapper())->map($xml_configuration_file, $configuration->test_suite(), $configuration->ignore_test_selection_in_xml_configuration() ? [] : $configuration->include_test_suites(), $configuration->ignore_test_selection_in_xml_configuration() ? [] : $configuration->exclude_test_suites());
        }
        Event_Facade::emitter()->test_suite_loaded(\Php_Unit\Event\Test_Suite\Test_Suite_Builder::from($test_suite));
        return $test_suite;
    }
    /**
     * @param non-empty-string       $path
     * @param list<non-empty-string> $suffixes
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function test_suite_from_path(string $path, array $suffixes, ?Test_Suite $suite = null): Test_Suite
    {
        if (str_ends_with($path, '.phpt') && is_file($path)) {
            if ($suite === null) {
                $suite = Test_Suite::empty($path);
            }
            $suite->add_test_file($path);
            return $suite;
        }
        if (is_dir($path)) {
            $files = (new File_Iterator_Facade())->get_files_as_array($path, $suffixes);
            if ($suite === null) {
                $suite = Test_Suite::empty('CLI Arguments');
            }
            $suite->add_test_files($files);
            return $suite;
        }
        try {
            $test_class = (new Test_Suite_Loader())->load($path);
        } catch (Exception $e) {
            print $e->get_message() . PHP_EOL;
            exit(1);
        }
        if ($suite === null) {
            return Test_Suite::from_class_reflector($test_class);
        }
        $suite->add_test_suite($test_class);
        return $suite;
    }
    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $suffixes
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function test_suite_from_path_list(array $paths, array $suffixes): Test_Suite
    {
        $suite = Test_Suite::empty('CLI Arguments');
        foreach ($paths as $path) {
            $this->test_suite_from_path($path, $suffixes, $suite);
        }
        return $suite;
    }
}