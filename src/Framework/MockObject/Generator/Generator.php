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
namespace Php_Unit\Framework\Mock_Object\Generator;

use function array_merge;
use function array_pop;
use function array_unique;
use function assert;
use function class_exists;
use function count;
use Exception;
use function explode;
use function implode;
use function in_array;
use function interface_exists;
use function is_array;
use Iterator;
use IteratorAggregate;
use function md5;
use function mt_rand;
use const PHP_EOL;
use Php_Unit\Framework\Mock_Object\Configurable_Method;
use Php_Unit\Framework\Mock_Object\Doubled_Clone_Method;
use Php_Unit\Framework\Mock_Object\Method;
use Php_Unit\Framework\Mock_Object\Mock_Object;
use Php_Unit\Framework\Mock_Object\Mock_Object_Api;
use Php_Unit\Framework\Mock_Object\Mock_Object_Internal;
use Php_Unit\Framework\Mock_Object\Proxied_Clone_Method;
use Php_Unit\Framework\Mock_Object\Stub;
use Php_Unit\Framework\Mock_Object\Stub_Api;
use Php_Unit\Framework\Mock_Object\Stub_Internal;
use Php_Unit\Framework\Mock_Object\Test_Double_State;
use function preg_match;
use Property_Hook_Type;
use ReflectionClass;
use ReflectionMethod;
use Reflection_Object;
use Sebastian_Bergmann\Type\Reflection_Mapper;
use Sebastian_Bergmann\Type\Type;
use function serialize;
use function sort;
use function sprintf;
use function substr;
use Throwable;
use function trait_exists;
use Traversable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator
{
    use Template_Loader;
    /**
     * @var null|non-empty-array<non-empty-string, true>
     */
    private static ?array $excluded_method_names = null;
    /**
     * @var array<non-empty-string, DoubledClass>
     */
    private static array $cache = [];
    /**
     * Returns a test double for the specified class.
     *
     * @param class-string            $type
     * @param ?list<non-empty-string> $methods
     * @param array<mixed>            $arguments
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    public function test_double(string $type, bool $mock_object, ?array $methods = [], array $arguments = [], string $mock_class_name = '', bool $call_original_constructor = true, bool $call_original_clone = true, bool $return_value_generation = true): Mock_Object|Stub
    {
        if ($type === Traversable::class) {
            $type = Iterator::class;
        }
        $this->ensure_known_type($type);
        $this->ensure_valid_methods($methods);
        $this->ensure_name_for_test_double_class_is_available($mock_class_name);
        $mock = $this->generate($type, $mock_object, $methods, $mock_class_name, $call_original_clone);
        $object = $this->instantiate($mock, $call_original_constructor, $arguments, $return_value_generation, $mock_object);
        assert($object instanceof $type);
        if ($mock_object) {
            assert($object instanceof Mock_Object);
        } else {
            assert($object instanceof Stub);
        }
        return $object;
    }
    /**
     * @param list<class-string> $interfaces
     *
     * @throws RuntimeException
     * @throws UnknownInterfaceException
     */
    public function test_double_for_interface_intersection(array $interfaces, bool $mock_object, bool $return_value_generation = true): Mock_Object|Stub
    {
        if (count($interfaces) < 2) {
            throw new RuntimeException('At least two interfaces must be specified');
        }
        foreach ($interfaces as $interface) {
            if (!interface_exists($interface)) {
                throw new Unknown_Interface_Exception($interface);
            }
        }
        sort($interfaces);
        $methods = [];
        foreach ($interfaces as $interface) {
            $methods = array_merge($methods, $this->names_of_methods_in($interface));
        }
        if (count(array_unique($methods)) < count($methods)) {
            throw new RuntimeException('Interfaces must not declare the same method');
        }
        $unqualified_names = [];
        foreach ($interfaces as $interface) {
            $parts = explode('\\', $interface);
            $unqualified_names[] = array_pop($parts);
        }
        sort($unqualified_names);
        do {
            $intersection_name = sprintf('Intersection_%s_%s', implode('_', $unqualified_names), substr(md5((string) mt_rand()), 0, 8));
        } while (interface_exists($intersection_name, false));
        $template = $this->load_template('intersection.tpl');
        $template->set_var(['intersection' => $intersection_name, 'interfaces' => implode(', ', $interfaces)]);
        eval($template->render());
        assert(interface_exists($intersection_name));
        return $this->test_double($intersection_name, $mock_object, returnValueGeneration: $return_value_generation);
    }
    /**
     * @param class-string            $type
     * @param ?list<non-empty-string> $methods
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ReflectionException
     * @throws RuntimeException
     *
     * @todo This method is only public because it is used to test generated code in PHPT tests
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/5476
     */
    public function generate(string $type, bool $mock_object, ?array $methods = null, string $mock_class_name = '', bool $call_original_clone = true): Doubled_Class
    {
        if ($mock_class_name !== '') {
            return $this->generate_code_for_test_double_class($type, $mock_object, $methods, $mock_class_name, $call_original_clone);
        }
        $key = md5($type . ($mock_object ? 'MockObject' : 'TestStub') . serialize($methods) . serialize($call_original_clone));
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = $this->generate_code_for_test_double_class($type, $mock_object, $methods, $mock_class_name, $call_original_clone);
        }
        return self::$cache[$key];
    }
    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     *
     * @return list<DoubledMethod>
     */
    private function mock_class_methods(string $class_name): array
    {
        $class = $this->reflect_class($class_name);
        $methods = [];
        foreach ($class->get_methods() as $method) {
            if (($method->is_public() || $method->is_abstract()) && $this->can_method_be_doubled($method)) {
                $methods[] = Doubled_Method::from_reflection($method);
            }
        }
        return $methods;
    }
    /**
     * @param class-string $interfaceName
     *
     * @throws ReflectionException
     *
     * @return list<ReflectionMethod>
     */
    private function user_defined_interface_methods(string $interface_name): array
    {
        $interface = $this->reflect_class($interface_name);
        $methods = [];
        foreach ($interface->get_methods() as $method) {
            if (!$method->is_user_defined()) {
                continue;
            }
            $methods[] = $method;
        }
        return $methods;
    }
    /**
     * @param array<mixed> $arguments
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function instantiate(Doubled_Class $mock_class, bool $call_original_constructor, array $arguments, bool $return_value_generation, bool $is_mock_object): object
    {
        $class_name = $mock_class->generate();
        try {
            $object = (new ReflectionClass($class_name))->new_instance_without_constructor();
            // @codeCoverageIgnoreStart
        } catch (\Reflection_Exception $e) {
            throw new Reflection_Exception($e->get_message(), $e->get_code(), $e);
            // @codeCoverageIgnoreEnd
        }
        $reflector = new Reflection_Object($object);
        /**
         * @noinspection PhpUnhandledExceptionInspection
         */
        $reflector->get_property('__phpunit_state')->set_value($object, new Test_Double_State($mock_class->configurable_methods(), $return_value_generation, $is_mock_object));
        if ($call_original_constructor && $reflector->get_constructor() !== null) {
            try {
                $reflector->get_constructor()->invoke_args($object, $arguments);
                // @codeCoverageIgnoreStart
            } catch (\Reflection_Exception $e) {
                throw new Reflection_Exception($e->get_message(), $e->get_code(), $e);
                // @codeCoverageIgnoreEnd
            }
        }
        return $object;
    }
    /**
     * @param class-string            $type
     * @param ?list<non-empty-string> $explicitMethods
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws MethodNamedMethodException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function generate_code_for_test_double_class(string $type, bool $mock_object, ?array $explicit_methods, string $mock_class_name, bool $call_original_clone): Doubled_Class
    {
        $class_template = $this->load_template('test_double_class.tpl');
        $additional_interfaces = [];
        $doubled_clone_method = false;
        $proxied_clone_method = false;
        $is_class = false;
        $is_readonly = false;
        $is_interface = false;
        $mock_methods = new Doubled_Method_Set();
        $test_double_class_prefix = $mock_object ? 'MockObject_' : 'TestStub_';
        $_mock_class_name = $this->generate_class_name($type, $mock_class_name, $test_double_class_prefix);
        if (class_exists($_mock_class_name['fullClassName'])) {
            $is_class = true;
        } elseif (interface_exists($_mock_class_name['fullClassName'])) {
            $is_interface = true;
        }
        $class = $this->reflect_class($_mock_class_name['fullClassName']);
        if ($class->is_enum()) {
            throw new Class_Is_Enumeration_Exception($_mock_class_name['fullClassName']);
        }
        if ($class->is_final()) {
            throw new Class_Is_Final_Exception($_mock_class_name['fullClassName']);
        }
        if ($class->is_read_only()) {
            $is_readonly = true;
        }
        // @see https://github.com/sebastianbergmann/phpunit/issues/2995
        if ($is_interface && $class->implements_interface(Throwable::class)) {
            $actual_class_name = Exception::class;
            $additional_interfaces[] = $class->get_name();
            $is_interface = false;
            $class = $this->reflect_class($actual_class_name);
            foreach ($this->user_defined_interface_methods($_mock_class_name['fullClassName']) as $method) {
                $method_name = $method->get_name();
                if ($class->has_method($method_name)) {
                    $class_method = $class->get_method($method_name);
                    if (!$this->can_method_be_doubled($class_method)) {
                        continue;
                    }
                }
                $mock_methods->add_methods(Doubled_Method::from_reflection($method));
            }
            $_mock_class_name = $this->generate_class_name($actual_class_name, $_mock_class_name['className'], $test_double_class_prefix);
        }
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
        if ($is_interface && $class->implements_interface(Traversable::class) && !$class->implements_interface(Iterator::class) && !$class->implements_interface(IteratorAggregate::class)) {
            $additional_interfaces[] = Iterator::class;
            $mock_methods->add_methods(...$this->mock_class_methods(Iterator::class));
        }
        if ($class->has_method('__clone')) {
            $clone_method = $class->get_method('__clone');
            if (!$clone_method->is_final()) {
                if ($call_original_clone && !$is_interface) {
                    $proxied_clone_method = true;
                } else {
                    $doubled_clone_method = true;
                }
            }
        } else {
            $doubled_clone_method = true;
        }
        if ($is_class && $explicit_methods === []) {
            $mock_methods->add_methods(...$this->mock_class_methods($_mock_class_name['fullClassName']));
        }
        if ($is_interface && ($explicit_methods === [] || $explicit_methods === null)) {
            $mock_methods->add_methods(...$this->interface_methods($_mock_class_name['fullClassName']));
        }
        if (is_array($explicit_methods)) {
            foreach ($explicit_methods as $method_name) {
                if ($class->has_method($method_name)) {
                    $method = $class->get_method($method_name);
                    if ($this->can_method_be_doubled($method)) {
                        $mock_methods->add_methods(Doubled_Method::from_reflection($method));
                    }
                } else {
                    $mock_methods->add_methods(Doubled_Method::from_name($_mock_class_name['fullClassName'], $method_name));
                }
            }
        }
        $properties_with_hooks = $this->properties($class);
        $configurable_methods = $this->configurable_methods($mock_methods, $properties_with_hooks);
        $mocked_methods = '';
        foreach ($mock_methods->as_array() as $mock_method) {
            $mocked_methods .= $mock_method->generate_code();
        }
        /** @var trait-string[] $traits */
        $traits = [Stub_Api::class];
        if ($mock_object) {
            $traits[] = Mock_Object_Api::class;
        }
        if ($mock_methods->has_method('method') || $class->has_method('method')) {
            throw new Method_Named_Method_Exception();
        }
        $traits[] = Method::class;
        if ($doubled_clone_method) {
            $traits[] = Doubled_Clone_Method::class;
        } elseif ($proxied_clone_method) {
            $traits[] = Proxied_Clone_Method::class;
        }
        $use_statements = '';
        foreach ($traits as $trait) {
            $use_statements .= sprintf('    use %s;' . PHP_EOL, $trait);
        }
        unset($traits);
        $class_template->set_var(['class_declaration' => $this->generate_test_double_class_declaration($mock_object, $_mock_class_name, $is_interface, $additional_interfaces, $is_readonly), 'use_statements' => $use_statements, 'mock_class_name' => $_mock_class_name['className'], 'methods' => $mocked_methods, 'property_hooks' => (new Hooked_Property_Generator())->generate($_mock_class_name['className'], $properties_with_hooks)]);
        return new Doubled_Class($class_template->render(), $_mock_class_name['className'], $configurable_methods);
    }
    /**
     * @param class-string $type
     *
     * @return array{className: class-string, originalClassName: class-string, fullClassName: class-string, namespaceName: string}
     */
    private function generate_class_name(string $type, string $class_name, string $prefix): array
    {
        if ($type[0] === '\\') {
            $type = substr($type, 1);
        }
        $class_name_parts = explode('\\', $type);
        if (count($class_name_parts) > 1) {
            $type = array_pop($class_name_parts);
            $namespace_name = implode('\\', $class_name_parts);
            $full_class_name = $namespace_name . '\\' . $type;
        } else {
            $namespace_name = '';
            $full_class_name = $type;
        }
        if ($class_name === '') {
            do {
                $class_name = $prefix . $type . '_' . substr(md5((string) mt_rand()), 0, 8);
            } while (class_exists($class_name, false));
        }
        return ['className' => $class_name, 'originalClassName' => $type, 'fullClassName' => $full_class_name, 'namespaceName' => $namespace_name];
    }
    /**
     * @param array{className: non-empty-string, originalClassName: non-empty-string, fullClassName: non-empty-string, namespaceName: string} $mockClassName
     * @param list<class-string>                                                                                                              $additionalInterfaces
     */
    private function generate_test_double_class_declaration(bool $mock_object, array $mock_class_name, bool $is_interface, array $additional_interfaces, bool $is_readonly): string
    {
        if ($mock_object) {
            $additional_interfaces[] = Mock_Object_Internal::class;
        } else {
            $additional_interfaces[] = Stub_Internal::class;
        }
        if ($is_readonly) {
            $buffer = 'readonly class ';
        } else {
            $buffer = 'class ';
        }
        $interfaces = implode(', ', $additional_interfaces);
        if ($is_interface) {
            $buffer .= sprintf('%s implements %s', $mock_class_name['className'], $interfaces);
            if (!in_array($mock_class_name['originalClassName'], $additional_interfaces, true)) {
                $buffer .= ', ';
                if ($mock_class_name['namespaceName'] !== '') {
                    $buffer .= $mock_class_name['namespaceName'] . '\\';
                }
                $buffer .= $mock_class_name['originalClassName'];
            }
        } else {
            $buffer .= sprintf('%s extends %s%s implements %s', $mock_class_name['className'], $mock_class_name['namespaceName'] !== '' ? $mock_class_name['namespaceName'] . '\\' : '', $mock_class_name['originalClassName'], $interfaces);
        }
        return $buffer;
    }
    private function can_method_be_doubled(ReflectionMethod $method): bool
    {
        if ($method->is_constructor()) {
            return false;
        }
        if ($method->is_destructor()) {
            return false;
        }
        if ($method->is_final()) {
            return false;
        }
        if ($method->is_private()) {
            return false;
        }
        return !$this->is_method_name_excluded($method->get_name());
    }
    private function is_method_name_excluded(string $name): bool
    {
        if (self::$excluded_method_names === null) {
            self::$excluded_method_names = ['__CLASS__' => true, '__DIR__' => true, '__FILE__' => true, '__FUNCTION__' => true, '__LINE__' => true, '__METHOD__' => true, '__NAMESPACE__' => true, '__TRAIT__' => true, '__clone' => true, '__halt_compiler' => true];
        }
        return isset(self::$excluded_method_names[$name]);
    }
    /**
     * @throws UnknownTypeException
     */
    private function ensure_known_type(string $type): void
    {
        if (!class_exists($type) && !interface_exists($type)) {
            throw new Unknown_Type_Exception($type);
        }
    }
    /**
     * @param ?list<non-empty-string> $methods
     *
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     */
    private function ensure_valid_methods(?array $methods): void
    {
        if ($methods === null) {
            return;
        }
        foreach ($methods as $method) {
            if (!preg_match('~[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*~', (string) $method)) {
                throw new Invalid_Method_Name_Exception((string) $method);
            }
        }
        if ($methods !== array_unique($methods)) {
            throw new Duplicate_Method_Exception($methods);
        }
    }
    /**
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     */
    private function ensure_name_for_test_double_class_is_available(string $class_name): void
    {
        if ($class_name === '') {
            return;
        }
        if (class_exists($class_name, false) || interface_exists($class_name, false) || trait_exists($class_name, false)) {
            throw new Name_Already_In_Use_Exception($class_name);
        }
    }
    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @throws ReflectionException
     *
     * @return ReflectionClass<T>
     *
     * @phpstan-ignore throws.unusedType
     */
    private function reflect_class(string $class_name): ReflectionClass
    {
        try {
            $class = new ReflectionClass($class_name);
            // @codeCoverageIgnoreStart
            /** @phpstan-ignore catch.neverThrown */
        } catch (\Reflection_Exception $e) {
            throw new Reflection_Exception($e->get_message(), $e->get_code(), $e);
        }
        // @codeCoverageIgnoreEnd
        return $class;
    }
    /**
     * @param class-string $classOrInterfaceName
     *
     * @throws ReflectionException
     *
     * @return list<string>
     */
    private function names_of_methods_in(string $class_or_interface_name): array
    {
        $class = $this->reflect_class($class_or_interface_name);
        $methods = [];
        foreach ($class->get_methods() as $method) {
            if ($method->is_public() || $method->is_abstract()) {
                $methods[] = $method->get_name();
            }
        }
        return $methods;
    }
    /**
     * @param class-string $interfaceName
     *
     * @throws ReflectionException
     *
     * @return list<DoubledMethod>
     */
    private function interface_methods(string $interface_name): array
    {
        $class = $this->reflect_class($interface_name);
        $methods = [];
        foreach ($class->get_methods() as $method) {
            $methods[] = Doubled_Method::from_reflection($method);
        }
        return $methods;
    }
    /**
     * @param list<HookedProperty> $propertiesWithHooks
     *
     * @return list<ConfigurableMethod>
     */
    private function configurable_methods(Doubled_Method_Set $methods, array $properties_with_hooks): array
    {
        $configurable = [];
        foreach ($methods->as_array() as $method) {
            $configurable[] = new Configurable_Method($method->method_name(), $method->default_parameter_values(), $method->number_of_parameters(), $method->return_type());
        }
        foreach ($properties_with_hooks as $property) {
            if ($property->has_get_hook()) {
                $configurable[] = new Configurable_Method(sprintf('$%s::get', $property->name()), [], 0, $property->type());
            }
            if ($property->has_set_hook()) {
                $configurable[] = new Configurable_Method(sprintf('$%s::set', $property->name()), [], 1, Type::from_name('void', false));
            }
        }
        return $configurable;
    }
    /**
     * @param ReflectionClass<object> $class
     *
     * @return list<HookedProperty>
     */
    private function properties(ReflectionClass $class): array
    {
        $mapper = new Reflection_Mapper();
        $properties = [];
        foreach ($class->get_properties() as $property) {
            if (!$property->is_public()) {
                continue;
            }
            if ($property->is_final()) {
                continue;
            }
            if (!$property->has_hooks()) {
                continue;
            }
            $has_get_hook = false;
            $has_set_hook = false;
            $set_hook_method_parameter_type = null;
            if ($property->has_hook(Property_Hook_Type::Get) && !$property->get_hook(Property_Hook_Type::Get)->is_final()) {
                $has_get_hook = true;
            }
            if ($property->has_hook(Property_Hook_Type::Set) && !$property->get_hook(Property_Hook_Type::Set)->is_final()) {
                $has_set_hook = true;
                $set_hook_method_parameter_type = $mapper->from_parameter_types($property->get_hook(Property_Hook_Type::Set))[0]->type();
            }
            if (!$has_get_hook && !$has_set_hook) {
                continue;
            }
            $properties[] = new Hooked_Property($property->get_name(), $mapper->from_property_type($property), $has_get_hook, $has_set_hook, $set_hook_method_parameter_type);
        }
        return $properties;
    }
}