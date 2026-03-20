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

use function array_key_exists;
use function assert;
use function count;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_iterable;
use function is_string;
use Php_Unit\Event;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Framework\Invalid_Data_Provider_Exception;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\Data_Provider as DataProviderMetadata;
use Php_Unit\Metadata\Data_Provider_Closure as DataProviderClosureMetadata;
use Php_Unit\Metadata\Metadata_Collection;
use Php_Unit\Metadata\Parser\Registry as MetadataRegistry;
use Php_Unit\Metadata\Test_With;
use Php_Unit\Util\Test;
use ReflectionMethod;
use function sprintf;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Data_Provider
{
    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @throws InvalidDataProviderException
     *
     * @return ?array<ProvidedData>
     */
    public function provided_data(string $class_name, string $method_name): ?array
    {
        $metadata_collection = Metadata_Registry::parser()->for_method($class_name, $method_name);
        $data_provider = $metadata_collection->is_data_provider();
        $data_provider_closure = $metadata_collection->is_data_provider_closure();
        $test_with = $metadata_collection->is_test_with();
        if ($data_provider->is_empty() && $data_provider_closure->is_empty() && $test_with->is_empty()) {
            return null;
        }
        $test_method = new ReflectionMethod($class_name, $method_name);
        if ($data_provider->is_not_empty() || $data_provider_closure->is_not_empty()) {
            if ($test_with->is_not_empty()) {
                $this->trigger_warning_for_mixing_of_data_provider_and_test_with($test_method);
            }
            return $this->data_provided_by_methods($class_name, $test_method, $data_provider, $data_provider_closure);
        }
        return $this->data_provided_by_metadata($test_method, $test_with);
    }
    /**
     * @param class-string<TestCase> $testClassName
     *
     * @throws InvalidDataProviderException
     *
     * @return array<ProvidedData>
     */
    private function data_provided_by_methods(string $test_class_name, ReflectionMethod $test_method, Metadata_Collection $data_provider, Metadata_Collection $data_provider_closure): array
    {
        $test_method_value_object = new Event\Code\Class_Method($test_class_name, $test_method->get_name());
        $methods_called = [];
        $result = [];
        $test_method_number_of_parameters = $test_method->get_number_of_parameters();
        $test_method_is_non_variadic = !$test_method->is_variadic();
        foreach ($data_provider as $_data_provider) {
            assert($_data_provider instanceof Data_Provider_Metadata);
            $provider_label = $_data_provider->class_name() . '::' . $_data_provider->method_name();
            $data_provider_method = new Event\Code\Class_Method($_data_provider->class_name(), $_data_provider->method_name());
            $validate_argument_count = $test_method_is_non_variadic && $_data_provider->validate_argument_count();
            Event\Facade::emitter()->data_provider_method_called($test_method_value_object, $data_provider_method);
            $methods_called[] = $data_provider_method;
            try {
                $method = new ReflectionMethod($_data_provider->class_name(), $_data_provider->method_name());
                $class_name = $_data_provider->class_name();
                $method_name = $_data_provider->method_name();
                if (Test::is_test_method($method)) {
                    Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Method %s::%s() used by test method %s::%s() is also a test method', $_data_provider->class_name(), $_data_provider->method_name(), $test_method->get_declaring_class()->get_name(), $test_method->get_name()));
                }
                if (!$method->is_public()) {
                    throw new Invalid_Data_Provider_Exception(sprintf('Data Provider method %s::%s() is not public', $class_name, $method_name));
                }
                if (!$method->is_static()) {
                    throw new Invalid_Data_Provider_Exception(sprintf('Data Provider method %s::%s() is not static', $class_name, $method_name));
                }
                if ($method->get_number_of_parameters() > 0) {
                    throw new Invalid_Data_Provider_Exception(sprintf('Data Provider method %s::%s() expects an argument', $class_name, $method_name));
                }
                /** @phpstan-ignore staticMethod.dynamicName */
                $data = $class_name::$method_name();
                if (!is_iterable($data)) {
                    throw new Invalid_Data_Provider_Exception(sprintf('Data Provider method %s::%s() does not return an iterable', $class_name, $method_name));
                }
            } catch (Throwable $e) {
                Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
                throw Invalid_Data_Provider_Exception::for_exception($e, $provider_label);
            }
            try {
                foreach ($data as $key => $value) {
                    if (!is_int($key) && !is_string($key)) {
                        throw new Invalid_Data_Provider_Exception(sprintf('The key must be an integer or a string, %s given', get_debug_type($key)));
                    }
                    if (!is_array($value)) {
                        throw new Invalid_Data_Provider_Exception(sprintf('Data set %s provided by %s is invalid, expected array but got %s', $this->format_key($key), $provider_label, get_debug_type($value)));
                    }
                    if ($validate_argument_count && $test_method_number_of_parameters < count($value)) {
                        $this->trigger_warning_for_argument_count($test_method, $this->format_key($key), $provider_label, count($value), $test_method_number_of_parameters);
                    }
                    if (is_int($key)) {
                        $result[] = new Provided_Data($provider_label, $value);
                        continue;
                    }
                    if (array_key_exists($key, $result)) {
                        throw new Invalid_Data_Provider_Exception(sprintf('The key "%s" has already been defined by provider %s', $key, $result[$key]->label()));
                    }
                    $result[$key] = new Provided_Data($provider_label, $value);
                }
            } catch (Throwable $e) {
                Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
                throw new Invalid_Data_Provider_Exception($e->get_message(), $e->get_code(), $e);
            }
        }
        foreach ($data_provider_closure as $_data_provider) {
            assert($_data_provider instanceof Data_Provider_Closure_Metadata);
            $provider_label = sprintf('callable provided to %s::%s()', $test_class_name, $test_method->get_name());
            $validate_argument_count = $test_method_is_non_variadic && $_data_provider->validate_argument_count();
            try {
                $callable = $_data_provider->closure();
                $data = $callable();
                if (!is_iterable($data)) {
                    throw new Invalid_Data_Provider_Exception('Data Provider callable does not return an iterable');
                }
            } catch (Throwable $e) {
                Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
                throw Invalid_Data_Provider_Exception::for_exception($e, $provider_label);
            }
            foreach ($data as $key => $value) {
                if (!is_int($key) && !is_string($key)) {
                    Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
                    throw new Invalid_Data_Provider_Exception(sprintf('The key must be an integer or a string, %s given', get_debug_type($key)));
                }
                if (!is_array($value)) {
                    Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
                    throw new Invalid_Data_Provider_Exception(sprintf('Data set %s provided by %s is invalid, expected array but got %s', $this->format_key($key), $provider_label, get_debug_type($value)));
                }
                if ($validate_argument_count && $test_method_number_of_parameters < count($value)) {
                    $this->trigger_warning_for_argument_count($test_method, $this->format_key($key), $provider_label, count($value), $test_method_number_of_parameters);
                }
                if (is_int($key)) {
                    $result[] = new Provided_Data($provider_label, $value);
                    continue;
                }
                if (array_key_exists($key, $result)) {
                    Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
                    throw new Invalid_Data_Provider_Exception(sprintf('The key "%s" has already been defined by provider %s', $key, $result[$key]->label()));
                }
                $result[$key] = new Provided_Data($provider_label, $value);
            }
        }
        Event\Facade::emitter()->data_provider_method_finished($test_method_value_object, ...$methods_called);
        if ($result === []) {
            throw new Invalid_Data_Provider_Exception('Empty data set provided by data provider');
        }
        return $result;
    }
    /**
     * @return array<ProvidedData>
     */
    private function data_provided_by_metadata(ReflectionMethod $test_method, Metadata_Collection $test_with): array
    {
        $result = [];
        foreach ($test_with as $i => $_test_with) {
            assert($_test_with instanceof Test_With);
            $provider_label = sprintf('TestWith#%s attribute', $i);
            if ($_test_with->has_name()) {
                $key = $_test_with->name();
                if (array_key_exists($key, $result)) {
                    throw new Invalid_Data_Provider_Exception(sprintf('The key "%s" has already been defined by %s', $key, $result[$key]->label()));
                }
                $result[$key] = new Provided_Data($provider_label, $_test_with->data());
            } else {
                $result[] = new Provided_Data($provider_label, $_test_with->data());
            }
        }
        $test_method_number_of_parameters = $test_method->get_number_of_parameters();
        $test_method_is_non_variadic = !$test_method->is_variadic();
        foreach ($result as $key => $provided_data) {
            $value = $provided_data->value();
            if (!is_array($value)) {
                throw new Invalid_Data_Provider_Exception(sprintf('Data set %s provided by %s is invalid, expected array but got %s', $this->format_key($key), $provided_data->label(), get_debug_type($value)));
            }
            if ($test_method_is_non_variadic && $test_method_number_of_parameters < count($value)) {
                $this->trigger_warning_for_argument_count($test_method, $this->format_key($key), $provided_data->label(), count($value), $test_method_number_of_parameters);
            }
        }
        return $result;
    }
    /**
     * @param int|non-empty-string $key
     *
     * @return non-empty-string
     */
    private function format_key(int|string $key): string
    {
        return is_int($key) ? '#' . $key : '"' . $key . '"';
    }
    private function trigger_warning_for_mixing_of_data_provider_and_test_with(ReflectionMethod $method): void
    {
        Event\Facade::emitter()->test_triggered_phpunit_warning($this->test_value_object($method), 'Mixing #[DataProvider*] and #[TestWith*] attributes is not supported, only the data provided by #[DataProvider*] will be used');
    }
    private function trigger_warning_for_argument_count(ReflectionMethod $method, string $key, string $label, int $number_of_values, int $test_method_number_of_parameters): void
    {
        Event\Facade::emitter()->test_triggered_phpunit_warning($this->test_value_object($method), sprintf('Data set %s provided by %s has more arguments (%d) than the test method accepts (%d)', $key, $label, $number_of_values, $test_method_number_of_parameters));
    }
    private function test_value_object(ReflectionMethod $method): Test_Method
    {
        return new Test_Method($method->get_declaring_class()->get_name(), $method->get_name(), $method->get_file_name(), $method->get_start_line(), Event\Code\Test_Dox_Builder::from_class_name_and_method_name($method->get_declaring_class()->get_name(), $method->get_name()), Metadata_Collection::from_array([]), Event\Test_Data\Test_Data_Collection::from_array([]));
    }
}