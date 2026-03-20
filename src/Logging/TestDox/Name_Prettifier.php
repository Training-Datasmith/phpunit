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
namespace Php_Unit\Logging\Test_Dox;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_pop;
use function array_values;
use function assert;
use function class_exists;
use function explode;
use function gettype;
use function implode;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use const PHP_EOL;
use Php_Unit\Event\Code\Test_Method_Builder;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\Parser\Registry as MetadataRegistry;
use Php_Unit\Metadata\Test_Dox;
use Php_Unit\Metadata\Test_Dox_Formatter;
use Php_Unit\Util\Color;
use Php_Unit\Util\Exporter;
use Php_Unit\Util\Filter;
use function preg_quote;
use function preg_replace;
use function preg_replace_callback_array;
use Reflection_Enum;
use ReflectionMethod;
use Reflection_Object;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;
use Throwable;
use function trim;
use function ucfirst;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Name_Prettifier
{
    /**
     * @var array<string, int>
     */
    private array $strings = [];
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $prettified_test_cases = [];
    /**
     * @var array<non-empty-string, true>
     */
    private array $errored_formatters = [];
    /**
     * @param class-string $className
     */
    public function prettify_test_class_name(string $class_name): string
    {
        if (class_exists($class_name)) {
            $class_level_test_dox = Metadata_Registry::parser()->for_class($class_name)->is_test_dox();
            if ($class_level_test_dox->is_not_empty()) {
                $class_level_test_dox = $class_level_test_dox->as_array()[0];
                assert($class_level_test_dox instanceof Test_Dox);
                return $class_level_test_dox->text();
            }
        }
        $parts = explode('\\', $class_name);
        $class_name = array_pop($parts);
        if (str_ends_with($class_name, 'Test')) {
            $class_name = substr($class_name, 0, strlen($class_name) - strlen('Test'));
        }
        if (str_starts_with($class_name, 'Tests')) {
            $class_name = substr($class_name, strlen('Tests'));
        } elseif (str_starts_with($class_name, 'Test')) {
            $class_name = substr($class_name, strlen('Test'));
        }
        if ($class_name === '') {
            $class_name = 'UnnamedTests';
        }
        if ($parts !== []) {
            $parts[] = $class_name;
            $fully_qualified_name = implode('\\', $parts);
        } else {
            $fully_qualified_name = $class_name;
        }
        $result = preg_replace('/(?<=[[:lower:]])(?=[[:upper:]])/u', ' ', $class_name);
        assert($result !== null);
        if ($fully_qualified_name !== $class_name) {
            return $result . ' (' . $fully_qualified_name . ')';
        }
        return $result;
    }
    // NOTE: this method is on a hot path and very performance sensitive. change with care.
    public function prettify_test_method_name(string $name): string
    {
        if ($name === '') {
            return '';
        }
        $string = rtrim($name, '0123456789');
        if (array_key_exists($string, $this->strings)) {
            $name = $string;
        } elseif ($string === $name) {
            $this->strings[$string] = 1;
        }
        if (str_starts_with($name, 'test_')) {
            $name = substr($name, 5);
        } elseif (str_starts_with($name, 'test')) {
            $name = substr($name, 4);
        }
        if ($name === '') {
            return '';
        }
        $name = ucfirst($name);
        $no_underscore = str_replace('_', ' ', $name);
        if ($no_underscore !== $name) {
            return trim($no_underscore);
        }
        $buffer = preg_replace_callback_array(['/(?!^)([A-Z])/' => static fn(array $matches): string => ' ' . strtolower((string) $matches[1]), '/(\d+)/' => static fn(array $matches): string => ' ' . $matches[1]], $name);
        return trim((string) $buffer);
    }
    public function prettify_test_case(Test_Case $test, bool $colorize): string
    {
        $key = $test::class . '#' . $test->name();
        if ($test->uses_data_provider()) {
            $key .= '#' . $test->data_name();
        }
        if ($colorize) {
            $key .= '#colorize';
        }
        if (isset($this->prettified_test_cases[$key])) {
            return $this->prettified_test_cases[$key];
        }
        $metadata_collection = Metadata_Registry::parser()->for_method($test::class, $test->name());
        $test_dox = $metadata_collection->is_test_dox()->is_method_level();
        $callback = $metadata_collection->is_test_dox_formatter();
        $is_customized = false;
        if ($test_dox->is_not_empty()) {
            $test_dox = $test_dox->as_array()[0];
            assert($test_dox instanceof Test_Dox);
            [$result, $is_customized] = $this->process_test_dox($test, $test_dox, $colorize);
        } elseif ($callback->is_not_empty()) {
            $callback = $callback->as_array()[0];
            assert($callback instanceof Test_Dox_Formatter);
            [$result, $is_customized] = $this->process_test_dox_formatter($test, $callback);
        } else {
            $result = $this->prettify_test_method_name($test->name());
        }
        if (!$is_customized && $test->uses_data_provider()) {
            $result .= $this->prettify_data_set($test, $colorize);
        }
        $this->prettified_test_cases[$key] = $result;
        return $result;
    }
    public function prettify_data_set(Test_Case $test, bool $colorize): string
    {
        if (!$colorize) {
            return $test->data_set_as_string();
        }
        if (is_int($test->data_name())) {
            return Color::dim(' with data set ') . Color::colorize('fg-cyan', (string) $test->data_name());
        }
        return Color::dim(' with ') . Color::colorize('fg-cyan', Color::visualize_whitespace($test->data_name()));
    }
    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function map_test_method_parameter_names_to_provided_data_values(Test_Case $test, bool $colorize): array
    {
        assert(method_exists($test, $test->name()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $reflector = new ReflectionMethod($test::class, $test->name());
        $provided_data = [];
        $provided_data_values = $test->provided_data();
        $i = 0;
        $provided_data['$_dataName'] = $test->data_name();
        foreach ($reflector->get_parameters() as $parameter) {
            if (array_key_exists($parameter->get_name(), $provided_data_values)) {
                $value = $provided_data_values[$parameter->get_name()];
            } elseif (array_key_exists($i, $provided_data_values)) {
                $value = $provided_data_values[$i];
            } elseif ($parameter->is_default_value_available()) {
                $value = $parameter->get_default_value();
            } else {
                $value = null;
            }
            $i++;
            if (is_object($value)) {
                $value = $this->object_to_string($value);
            }
            if (!is_scalar($value)) {
                $value = gettype($value);
                if ($value === 'NULL') {
                    $value = 'null';
                }
            }
            if (is_bool($value) || is_int($value) || is_float($value)) {
                $value = Exporter::export($value);
            }
            if ($value === '') {
                if ($colorize) {
                    $value = Color::colorize('dim,underlined', 'empty');
                } else {
                    $value = "''";
                }
            }
            $provided_data['$' . $parameter->get_name()] = str_replace('$', '\$', $value);
        }
        if ($colorize) {
            return array_map(static fn(mixed $value): string => Color::colorize('fg-cyan', Color::visualize_whitespace((string) $value, true)), $provided_data);
        }
        return $provided_data;
    }
    private function object_to_string(object $value): string
    {
        $reflector = new Reflection_Object($value);
        if ($reflector->is_enum()) {
            $enum_reflector = new Reflection_Enum($value);
            if ($enum_reflector->is_backed()) {
                return (string) $value->value;
            }
            return (string) $value->name;
        }
        if ($reflector->has_method('__toString')) {
            return (string) $value;
        }
        return $value::class;
    }
    /**
     * @return array{0: string, 1: bool}
     */
    private function process_test_dox(Test_Case $test, Test_Dox $test_dox, bool $colorize): array
    {
        $placeholders_used = false;
        $result = $test_dox->text();
        if (str_contains($result, '$')) {
            $annotation = $result;
            $provided_data = $this->map_test_method_parameter_names_to_provided_data_values($test, $colorize);
            $variables = array_map(static fn(string $variable): string => sprintf('/%s(?=\b)/', preg_quote($variable, '/')), array_keys($provided_data));
            $result = preg_replace($variables, $provided_data, $annotation);
            $placeholders_used = true;
        }
        assert($result !== null);
        return [$result, $placeholders_used];
    }
    /**
     * @return array{0: string, 1: bool}
     */
    private function process_test_dox_formatter(Test_Case $test, Test_Dox_Formatter $formatter): array
    {
        $class_name = $formatter->class_name();
        $method_name = $formatter->method_name();
        $formatter_identifier = $class_name . '::' . $method_name;
        if (isset($this->errored_formatters[$formatter_identifier])) {
            return [$this->prettify_test_method_name($test->name()), false];
        }
        if (!method_exists($class_name, $method_name)) {
            Event_Facade::emitter()->test_triggered_phpunit_error(Test_Method_Builder::from_test_case($test, false), sprintf('Method %s::%s() cannot be used as a TestDox formatter because it does not exist', $class_name, $method_name));
            $this->errored_formatters[$formatter_identifier] = true;
            return [$this->prettify_test_method_name($test->name()), false];
        }
        $reflector = new ReflectionMethod($class_name, $method_name);
        if (!$reflector->is_public()) {
            Event_Facade::emitter()->test_triggered_phpunit_error(Test_Method_Builder::from_test_case($test, false), sprintf('Method %s::%s() cannot be used as a TestDox formatter because it is not public', $class_name, $method_name));
            $this->errored_formatters[$formatter_identifier] = true;
            return [$this->prettify_test_method_name($test->name()), false];
        }
        if (!$reflector->is_static()) {
            Event_Facade::emitter()->test_triggered_phpunit_error(Test_Method_Builder::from_test_case($test, false), sprintf('Method %s::%s() cannot be used as a TestDox formatter because it is not static', $class_name, $method_name));
            $this->errored_formatters[$formatter_identifier] = true;
            return [$this->prettify_test_method_name($test->name()), false];
        }
        try {
            $result = $reflector->invoke_args(null, array_values($test->provided_data()));
            assert(is_string($result));
            return [$result, true];
        } catch (Throwable $t) {
            Event_Facade::emitter()->test_triggered_phpunit_error(Test_Method_Builder::from_test_case($test, false), sprintf('TestDox formatter %s::%s() triggered an error: %s%s%s', $class_name, $method_name, $t->get_message(), PHP_EOL, Filter::stack_trace_from_throwable_as_string($t)));
            $this->errored_formatters[$formatter_identifier] = true;
            return [$this->prettify_test_method_name($test->name()), false];
        }
    }
}