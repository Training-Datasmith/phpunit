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
namespace Php_Unit\Util;

use function array_keys;
use function array_merge;
use function array_reverse;
use function assert;
use Php_Unit\Framework\Assert;
use Php_Unit\Framework\Test_Case;
use ReflectionClass;
use Reflection_Exception;
use ReflectionMethod;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Reflection
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return array{file: non-empty-string, line: non-negative-int}
     */
    public static function source_location_for(string $class_name, string $method_name): array
    {
        try {
            $reflector = new ReflectionMethod($class_name, $method_name);
            $file = $reflector->get_file_name();
            $line = $reflector->get_start_line();
        } catch (Reflection_Exception) {
            $file = 'unknown';
            $line = 0;
        }
        assert($file !== false && $file !== '');
        assert($line !== false && $line >= 0);
        return ['file' => $file, 'line' => $line];
    }
    /**
     * @param ReflectionClass<TestCase> $class
     *
     * @return list<ReflectionMethod>
     */
    public static function public_methods_declared_directly_in_test_class(ReflectionClass $class): array
    {
        return self::filter_and_sort_methods($class, ReflectionMethod::IS_PUBLIC, true);
    }
    /**
     * @param ReflectionClass<TestCase> $class
     *
     * @return list<ReflectionMethod>
     */
    public static function methods_declared_directly_in_test_class(ReflectionClass $class): array
    {
        return self::filter_and_sort_methods($class, null, false);
    }
    /**
     * @param ReflectionClass<TestCase> $class
     *
     * @return list<ReflectionMethod>
     */
    private static function filter_and_sort_methods(ReflectionClass $class, ?int $filter, bool $sort_highest_to_lowest): array
    {
        $methods_by_class = [];
        foreach ($class->get_methods($filter) as $method) {
            $declaring_class_name = $method->get_declaring_class()->get_name();
            if ($declaring_class_name === Test_Case::class) {
                continue;
            }
            if ($declaring_class_name === Assert::class) {
                continue;
            }
            if (!isset($methods_by_class[$declaring_class_name])) {
                $methods_by_class[$declaring_class_name] = [];
            }
            $methods_by_class[$declaring_class_name][] = $method;
        }
        $class_names = array_keys($methods_by_class);
        if ($sort_highest_to_lowest) {
            $class_names = array_reverse($class_names);
        }
        $methods = [];
        foreach ($class_names as $class_name) {
            $methods = array_merge($methods, $methods_by_class[$class_name]);
        }
        return $methods;
    }
}