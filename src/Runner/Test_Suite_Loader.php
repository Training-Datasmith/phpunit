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
namespace Php_Unit\Runner;

use function array_diff;
use function basename;
use function get_declared_classes;
use Php_Unit\Framework\Test_Case;
use function realpath;
use ReflectionClass;
use function str_ends_with;
use function strpos;
use function strtolower;
use function substr;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Suite_Loader
{
    /**
     * @var list<class-string>
     */
    private static array $declared_classes = [];
    /**
     * @var array<non-empty-string, list<class-string>>
     */
    private static array $file_to_classes_map = [];
    /**
     * @throws Exception
     *
     * @return ReflectionClass<TestCase>
     */
    public function load(string $suite_class_file): ReflectionClass
    {
        $suite_class_file = realpath($suite_class_file);
        $suite_class_name = $this->class_name_from_file_name($suite_class_file);
        $loaded_classes = $this->load_suite_class_file($suite_class_file);
        foreach ($loaded_classes as $class_name) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $class = new ReflectionClass($class_name);
            if ($class->is_anonymous()) {
                continue;
            }
            if ($class->get_file_name() !== $suite_class_file) {
                continue;
            }
            if (!$class->is_subclass_of(Test_Case::class)) {
                continue;
            }
            if (!str_ends_with(strtolower($class->get_short_name()), strtolower($suite_class_name))) {
                continue;
            }
            if (!$class->is_abstract()) {
                return $class;
            }
            $e = new Class_Is_Abstract_Exception($class->get_name(), $suite_class_file);
        }
        if (isset($e)) {
            throw $e;
        }
        foreach ($loaded_classes as $class_name) {
            if (str_ends_with(strtolower($class_name), strtolower($suite_class_name))) {
                throw new Class_Does_Not_Extend_Test_Case_Exception($class_name, $suite_class_file);
            }
        }
        throw new Class_Cannot_Be_Found_Exception($suite_class_name, $suite_class_file);
    }
    private function class_name_from_file_name(string $suite_class_file): string
    {
        $class_name = basename($suite_class_file, '.php');
        $dot_pos = strpos($class_name, '.');
        if ($dot_pos !== false) {
            return substr($class_name, 0, $dot_pos);
        }
        return $class_name;
    }
    /**
     * @return array<class-string>
     */
    private function load_suite_class_file(string $suite_class_file): array
    {
        if (isset(self::$file_to_classes_map[$suite_class_file])) {
            return self::$file_to_classes_map[$suite_class_file];
        }
        if (self::$declared_classes === []) {
            self::$declared_classes = get_declared_classes();
        }
        require_once $suite_class_file;
        $loaded_classes = array_diff(get_declared_classes(), self::$declared_classes);
        foreach ($loaded_classes as $loaded_class) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $class = new ReflectionClass($loaded_class);
            if (!isset(self::$file_to_classes_map[$class->get_file_name()])) {
                self::$file_to_classes_map[$class->get_file_name()] = [];
            }
            self::$file_to_classes_map[$class->get_file_name()][] = $class->get_name();
        }
        self::$declared_classes = get_declared_classes();
        if ($loaded_classes === []) {
            return self::$declared_classes;
        }
        return $loaded_classes;
    }
}