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
namespace Php_Unit\Framework\Mock_Object;

use function array_all;
use function array_map;
use function explode;
use function in_array;
use function interface_exists;
use Php_Unit\Framework\Mock_Object\Generator\Generator;
use ReflectionClass;
use Reflection_Object;
use function sprintf;
use stdClass;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Return_Value_Generator
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws Exception
     */
    public function generate(string $class_name, string $method_name, Stub_Internal $test_stub, string $return_type): mixed
    {
        $intersection = false;
        $union = false;
        if (str_contains($return_type, '|')) {
            $types = explode('|', $return_type);
            $union = true;
            foreach ($types as $key => $type) {
                if (str_starts_with($type, '(') && str_ends_with($type, ')')) {
                    $types[$key] = substr($type, 1, -1);
                }
            }
        } elseif (str_contains($return_type, '&')) {
            $types = explode('&', $return_type);
            $intersection = true;
        } else {
            $types = [$return_type];
        }
        if (!$intersection) {
            $lower_types = array_map(strtolower(...), $types);
            if (in_array('', $lower_types, true) || in_array('null', $lower_types, true) || in_array('mixed', $lower_types, true) || in_array('void', $lower_types, true)) {
                return null;
            }
            if (in_array('true', $lower_types, true)) {
                return true;
            }
            if (in_array('false', $lower_types, true) || in_array('bool', $lower_types, true)) {
                return false;
            }
            if (in_array('float', $lower_types, true)) {
                return 0.0;
            }
            if (in_array('int', $lower_types, true)) {
                return 0;
            }
            if (in_array('string', $lower_types, true)) {
                return '';
            }
            if (in_array('array', $lower_types, true)) {
                return [];
            }
            if (in_array('static', $lower_types, true)) {
                return $this->new_instance_of($test_stub, $class_name, $method_name);
            }
            if (in_array('object', $lower_types, true)) {
                return new stdClass();
            }
            if (in_array('callable', $lower_types, true) || in_array('closure', $lower_types, true)) {
                return static function (): void {
                };
            }
            if (in_array('traversable', $lower_types, true) || in_array('generator', $lower_types, true) || in_array('iterable', $lower_types, true)) {
                $generator = static function (): \Generator {
                    yield from [];
                };
                return $generator();
            }
            if (!$union) {
                return $this->test_double_for($return_type, $class_name, $method_name);
            }
        }
        if ($union) {
            foreach ($types as $type) {
                if (str_contains($type, '&')) {
                    $_types = explode('&', $type);
                    if ($this->only_interfaces($_types)) {
                        return $this->test_double_for_intersection_of_interfaces($_types, $class_name, $method_name);
                    }
                }
            }
        }
        if ($intersection && $this->only_interfaces($types)) {
            return $this->test_double_for_intersection_of_interfaces($types, $class_name, $method_name);
        }
        $reason = '';
        if ($union) {
            $reason = ' because the declared return type is a union';
        } elseif ($intersection) {
            $reason = ' because the declared return type is an intersection';
        }
        throw new RuntimeException(sprintf('Return value for %s::%s() cannot be generated%s, please configure a return value for this method', $class_name, $method_name, $reason));
    }
    /**
     * @param non-empty-list<string> $types
     */
    private function only_interfaces(array $types): bool
    {
        return array_all($types, static fn(string $type): bool => interface_exists($type));
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws RuntimeException
     */
    private function new_instance_of(Stub_Internal $test_stub, string $class_name, string $method_name): Stub
    {
        try {
            $object = (new ReflectionClass($test_stub::class))->new_instance_without_constructor();
            $reflector = new Reflection_Object($object);
            $reflector->get_property('__phpunit_state')->set_value($object, new Test_Double_State($test_stub->__phpunit_state()->configurable_methods(), $test_stub->__phpunit_state()->generate_return_values()));
            return $object;
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            throw new RuntimeException(sprintf('Return value for %s::%s() cannot be generated: %s', $class_name, $method_name, $t->get_message()));
            // @codeCoverageIgnoreEnd
        }
    }
    /**
     * @param class-string     $type
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws RuntimeException
     */
    private function test_double_for(string $type, string $class_name, string $method_name): Stub
    {
        try {
            return (new Generator())->test_double($type, false, [], [], '', false);
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            throw new RuntimeException(sprintf('Return value for %s::%s() cannot be generated: %s', $class_name, $method_name, $t->get_message()));
            // @codeCoverageIgnoreEnd
        }
    }
    /**
     * @param non-empty-list<string> $types
     * @param class-string           $className
     * @param non-empty-string       $methodName
     *
     * @throws RuntimeException
     */
    private function test_double_for_intersection_of_interfaces(array $types, string $class_name, string $method_name): Stub
    {
        try {
            return (new Generator())->test_double_for_interface_intersection($types, false);
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            throw new RuntimeException(sprintf('Return value for %s::%s() cannot be generated: %s', $class_name, $method_name, $t->get_message()));
            // @codeCoverageIgnoreEnd
        }
    }
}