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
namespace Php_Unit\Test_Runner\Test_Result;

use function array_merge;
use function assert;
use function explode;
use function in_array;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Framework\Test_Size\Known;
use Php_Unit\Framework\Test_Size\Test_Size;
use Php_Unit\Metadata\Api\Groups;
use ReflectionMethod;
use ReflectionNamedType;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Passed_Tests
{
    private static ?self $instance = null;
    /**
     * @var list<class-string>
     */
    private array $passed_test_classes = [];
    /**
     * @var array<string,array{returnValue: mixed, size: TestSize}>
     */
    private array $passed_test_methods = [];
    public static function instance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }
    /**
     * @param class-string $className
     */
    public function test_class_passed(string $class_name): void
    {
        $this->passed_test_classes[] = $class_name;
    }
    public function test_method_passed(Test_Method $test, mixed $return_value): void
    {
        $size = (new Groups())->size($test->class_name(), $test->method_name());
        $this->passed_test_methods[$test->class_name() . '::' . $test->method_name()] = ['returnValue' => $return_value, 'size' => $size];
    }
    public function import(self $other): void
    {
        $this->passed_test_classes = array_merge($this->passed_test_classes, $other->passed_test_classes);
        $this->passed_test_methods = array_merge($this->passed_test_methods, $other->passed_test_methods);
    }
    /**
     * @param class-string $className
     */
    public function has_test_class_passed(string $class_name): bool
    {
        return in_array($class_name, $this->passed_test_classes, true);
    }
    public function has_test_method_passed(string $method): bool
    {
        return isset($this->passed_test_methods[$method]);
    }
    public function is_greater_than(string $method, Test_Size $other): bool
    {
        if ($other->is_unknown()) {
            return false;
        }
        assert($other instanceof Known);
        $size = $this->passed_test_methods[$method]['size'];
        if ($size->is_unknown()) {
            return false;
        }
        assert($size instanceof Known);
        return $size->is_greater_than($other);
    }
    public function has_return_value(string $method): bool
    {
        $return_type = (new ReflectionMethod(...explode('::', $method)))->get_return_type();
        return !$return_type instanceof ReflectionNamedType || !in_array($return_type->get_name(), ['never', 'void'], true);
    }
    public function return_value(string $method): mixed
    {
        if (isset($this->passed_test_methods[$method])) {
            return $this->passed_test_methods[$method]['returnValue'];
        }
        return null;
    }
}