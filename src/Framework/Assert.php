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
namespace Php_Unit\Framework;

use function array_combine;
use function array_intersect_key;
use ArrayAccess;
use function class_exists;
use function count;
use Countable;
use function file_get_contents;
use Generator;
use function interface_exists;
use function is_bool;
use Php_Unit\Framework\Constraint\Array_Has_Key;
use Php_Unit\Framework\Constraint\Arrays_Are_Equal;
use Php_Unit\Framework\Constraint\Arrays_Are_Identical;
use Php_Unit\Framework\Constraint\Callback;
use Php_Unit\Framework\Constraint\Constraint;
use Php_Unit\Framework\Constraint\Count;
use Php_Unit\Framework\Constraint\Directory_Exists;
use Php_Unit\Framework\Constraint\File_Exists;
use Php_Unit\Framework\Constraint\Greater_Than;
use Php_Unit\Framework\Constraint\Is_Anything;
use Php_Unit\Framework\Constraint\Is_Empty;
use Php_Unit\Framework\Constraint\Is_Equal;
use Php_Unit\Framework\Constraint\Is_Equal_Canonicalizing;
use Php_Unit\Framework\Constraint\Is_Equal_Ignoring_Case;
use Php_Unit\Framework\Constraint\Is_Equal_With_Delta;
use Php_Unit\Framework\Constraint\Is_False;
use Php_Unit\Framework\Constraint\Is_Finite;
use Php_Unit\Framework\Constraint\Is_Identical;
use Php_Unit\Framework\Constraint\Is_Infinite;
use Php_Unit\Framework\Constraint\Is_Instance_Of;
use Php_Unit\Framework\Constraint\Is_Json;
use Php_Unit\Framework\Constraint\Is_List;
use Php_Unit\Framework\Constraint\Is_Nan;
use Php_Unit\Framework\Constraint\Is_Null;
use Php_Unit\Framework\Constraint\Is_Readable;
use Php_Unit\Framework\Constraint\Is_True;
use Php_Unit\Framework\Constraint\Is_Type;
use Php_Unit\Framework\Constraint\Is_Writable;
use Php_Unit\Framework\Constraint\Json_Matches;
use Php_Unit\Framework\Constraint\Less_Than;
use Php_Unit\Framework\Constraint\Logical_And;
use Php_Unit\Framework\Constraint\Logical_Not;
use Php_Unit\Framework\Constraint\Logical_Or;
use Php_Unit\Framework\Constraint\Logical_Xor;
use Php_Unit\Framework\Constraint\Object_Equals;
use Php_Unit\Framework\Constraint\Object_Has_Property;
use Php_Unit\Framework\Constraint\Regular_Expression;
use Php_Unit\Framework\Constraint\Same_Size;
use Php_Unit\Framework\Constraint\String_Contains;
use Php_Unit\Framework\Constraint\String_Ends_With;
use Php_Unit\Framework\Constraint\String_Equals_String_Ignoring_Line_Endings;
use Php_Unit\Framework\Constraint\String_Matches_Format_Description;
use Php_Unit\Framework\Constraint\String_Starts_With;
use Php_Unit\Framework\Constraint\Traversable_Contains_Equal;
use Php_Unit\Framework\Constraint\Traversable_Contains_Identical;
use Php_Unit\Framework\Constraint\Traversable_Contains_Only;
use Php_Unit\Util\Xml\Loader as XmlLoader;
use Php_Unit\Util\Xml\Xml_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class Assert
{
    private static int $count = 0;
    /**
     * Asserts that two arrays are equal while only considering a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeConsidered
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_array_is_equal_to_array_only_considering_list_of_keys(array $expected, array $actual, array $keys_to_be_considered, string $message = ''): void
    {
        $filtered_expected = [];
        foreach ($keys_to_be_considered as $key) {
            if (isset($expected[$key])) {
                $filtered_expected[$key] = $expected[$key];
            }
        }
        $filtered_actual = [];
        foreach ($keys_to_be_considered as $key) {
            if (isset($actual[$key])) {
                $filtered_actual[$key] = $actual[$key];
            }
        }
        self::assert_equals($filtered_expected, $filtered_actual, $message);
    }
    /**
     * Asserts that two arrays are equal while ignoring a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeIgnored
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_array_is_equal_to_array_ignoring_list_of_keys(array $expected, array $actual, array $keys_to_be_ignored, string $message = ''): void
    {
        foreach ($keys_to_be_ignored as $key) {
            unset($expected[$key], $actual[$key]);
        }
        self::assert_equals($expected, $actual, $message);
    }
    /**
     * Asserts that two arrays are identical while only considering a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeConsidered
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_array_is_identical_to_array_only_considering_list_of_keys(array $expected, array $actual, array $keys_to_be_considered, string $message = ''): void
    {
        $keys_to_be_considered = array_combine($keys_to_be_considered, $keys_to_be_considered);
        $expected = array_intersect_key($expected, $keys_to_be_considered);
        $actual = array_intersect_key($actual, $keys_to_be_considered);
        self::assert_same($expected, $actual, $message);
    }
    /**
     * Asserts that two arrays are equal while ignoring a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeIgnored
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_array_is_identical_to_array_ignoring_list_of_keys(array $expected, array $actual, array $keys_to_be_ignored, string $message = ''): void
    {
        foreach ($keys_to_be_ignored as $key) {
            unset($expected[$key], $actual[$key]);
        }
        self::assert_same($expected, $actual, $message);
    }
    /**
     * Asserts that an array has a specified key.
     *
     * @param array<mixed>|ArrayAccess<array-key, mixed> $array
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_array_has_key(mixed $key, array|ArrayAccess $array, string $message = ''): void
    {
        $constraint = new Array_Has_Key($key);
        self::assert_that($array, $constraint, $message);
    }
    /**
     * Asserts that an array does not have a specified key.
     *
     * @param array<mixed>|ArrayAccess<array-key, mixed> $array
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_array_not_has_key(mixed $key, array|ArrayAccess $array, string $message = ''): void
    {
        $constraint = new Logical_Not(new Array_Has_Key($key));
        self::assert_that($array, $constraint, $message);
    }
    /**
     * @phpstan-assert list<mixed> $array
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_is_list(mixed $array, string $message = ''): void
    {
        self::assert_that($array, new Is_List(), $message);
    }
    /**
     * Assert that two arrays are identical.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array matters, and keys as well as values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_arrays_are_identical(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Identical($expected, true, true), $message);
    }
    /**
     * Assert that two arrays are identical while ignoring the order of their values.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array does not matter, and keys as well as values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_arrays_are_identical_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Identical($expected, true, false), $message);
    }
    /**
     * Assert that two arrays have identical values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array matters, and values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_arrays_have_identical_values(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Identical($expected, false, true), $message);
    }
    /**
     * Assert that two arrays have identical values while ignoring the order of these values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array does not matter, and values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_arrays_have_identical_values_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Identical($expected, false, false), $message);
    }
    /**
     * Assert that two arrays are equal.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array matters, and keys as well as values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     */
    final public static function assert_arrays_are_equal(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Equal($expected, true, true), $message);
    }
    /**
     * Assert that two arrays are equal while ignoring the order of their values.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array does not matter, and keys as well as values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_arrays_are_equal_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Equal($expected, true, false), $message);
    }
    /**
     * Assert that two arrays have equal values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array matters, and values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     */
    final public static function assert_arrays_have_equal_values(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Equal($expected, false, true), $message);
    }
    /**
     * Assert that two arrays have equal values while ignoring the order of these values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array does not matter, and values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_arrays_have_equal_values_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        self::assert_that($actual, new Arrays_Are_Equal($expected, false, false), $message);
    }
    /**
     * Asserts that a haystack contains a needle.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_contains(mixed $needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new Traversable_Contains_Identical($needle);
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_equals(mixed $needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new Traversable_Contains_Equal($needle);
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_not_contains(mixed $needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new Logical_Not(new Traversable_Contains_Identical($needle));
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_not_contains_equals(mixed $needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new Logical_Not(new Traversable_Contains_Equal($needle));
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * Asserts that a haystack contains only values of type array.
     *
     * @phpstan-assert iterable<array<mixed>> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_array(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Array), $message);
    }
    /**
     * Asserts that a haystack contains only values of type bool.
     *
     * @phpstan-assert iterable<bool> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_bool(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Bool), $message);
    }
    /**
     * Asserts that a haystack contains only values of type callable.
     *
     * @phpstan-assert iterable<callable> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_callable(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Callable), $message);
    }
    /**
     * Asserts that a haystack contains only values of type float.
     *
     * @phpstan-assert iterable<float> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_float(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Float), $message);
    }
    /**
     * Asserts that a haystack contains only values of type int.
     *
     * @phpstan-assert iterable<int> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_int(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Int), $message);
    }
    /**
     * Asserts that a haystack contains only values of type iterable.
     *
     * @phpstan-assert iterable<iterable<mixed>> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_iterable(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Iterable), $message);
    }
    /**
     * Asserts that a haystack contains only values of type null.
     *
     * @phpstan-assert iterable<null> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_null(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Null), $message);
    }
    /**
     * Asserts that a haystack contains only values of type numeric.
     *
     * @phpstan-assert iterable<numeric> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_numeric(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Numeric), $message);
    }
    /**
     * Asserts that a haystack contains only values of type object.
     *
     * @phpstan-assert iterable<object> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_object(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Object), $message);
    }
    /**
     * Asserts that a haystack contains only values of type resource.
     *
     * @phpstan-assert iterable<resource> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_resource(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Resource), $message);
    }
    /**
     * Asserts that a haystack contains only values of type closed resource.
     *
     * @phpstan-assert iterable<resource> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_closed_resource(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::ClosedResource), $message);
    }
    /**
     * Asserts that a haystack contains only values of type scalar.
     *
     * @phpstan-assert iterable<scalar> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_scalar(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::Scalar), $message);
    }
    /**
     * Asserts that a haystack contains only values of type string.
     *
     * @phpstan-assert iterable<string> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_string(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_native_type(Native_Type::String), $message);
    }
    /**
     * Asserts that a haystack contains only instances of a specified interface or class name.
     *
     * @template T
     *
     * @phpstan-assert iterable<T> $haystack
     *
     * @param class-string<T> $className
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_only_instances_of(string $class_name, iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, Traversable_Contains_Only::for_class_or_interface($class_name), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type array.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_array(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Array)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type bool.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_bool(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Bool)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type callable.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_callable(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Callable)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type float.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_float(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Float)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type int.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_int(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Int)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type iterable.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_iterable(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Iterable)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type null.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_null(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Null)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type numeric.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_numeric(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Numeric)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type object.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_object(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Object)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type resource.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_resource(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Resource)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type closed resource.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_closed_resource(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::ClosedResource)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type scalar.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_scalar(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::Scalar)), $message);
    }
    /**
     * Asserts that a haystack does not contain only values of type string.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_string(iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_native_type(Native_Type::String)), $message);
    }
    /**
     * Asserts that a haystack does not contain only instances of a specified interface or class name.
     *
     * @param class-string    $className
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_contains_not_only_instances_of(string $class_name, iterable $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new Logical_Not(Traversable_Contains_Only::for_class_or_interface($class_name)), $message);
    }
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param Countable|iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     */
    final public static function assert_count(int $expected_count, Countable|iterable $haystack, string $message = ''): void
    {
        if ($haystack instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$haystack');
        }
        self::assert_that($haystack, new Count($expected_count), $message);
    }
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param Countable|iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     */
    final public static function assert_not_count(int $expected_count, Countable|iterable $haystack, string $message = ''): void
    {
        if ($haystack instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$haystack');
        }
        $constraint = new Logical_Not(new Count($expected_count));
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * Asserts that two variables are equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_equals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $constraint = new Is_Equal($expected);
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are equal (canonicalizing).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_equals_canonicalizing(mixed $expected, mixed $actual, string $message = ''): void
    {
        $constraint = new Is_Equal_Canonicalizing($expected);
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are equal (ignoring case).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_equals_ignoring_case(mixed $expected, mixed $actual, string $message = ''): void
    {
        $constraint = new Is_Equal_Ignoring_Case($expected);
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are equal (with delta).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_equals_with_delta(mixed $expected, mixed $actual, float $delta, string $message = ''): void
    {
        $constraint = new Is_Equal_With_Delta($expected, $delta);
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are not equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_not_equals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $constraint = new Logical_Not(new Is_Equal($expected));
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are not equal (canonicalizing).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_not_equals_canonicalizing(mixed $expected, mixed $actual, string $message = ''): void
    {
        $constraint = new Logical_Not(new Is_Equal_Canonicalizing($expected));
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are not equal (ignoring case).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_not_equals_ignoring_case(mixed $expected, mixed $actual, string $message = ''): void
    {
        $constraint = new Logical_Not(new Is_Equal_Ignoring_Case($expected));
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * Asserts that two variables are not equal (with delta).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_not_equals_with_delta(mixed $expected, mixed $actual, float $delta, string $message = ''): void
    {
        $constraint = new Logical_Not(new Is_Equal_With_Delta($expected, $delta));
        self::assert_that($actual, $constraint, $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_object_equals(object $expected, object $actual, string $method = 'equals', string $message = ''): void
    {
        self::assert_that($actual, self::object_equals($expected, $method), $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_object_not_equals(object $expected, object $actual, string $method = 'equals', string $message = ''): void
    {
        self::assert_that($actual, self::logical_not(self::object_equals($expected, $method)), $message);
    }
    /**
     * Asserts that a variable is empty.
     *
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     */
    final public static function assert_empty(mixed $actual, string $message = ''): void
    {
        if ($actual instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$actual');
        }
        self::assert_that($actual, self::is_empty(), $message);
    }
    /**
     * Asserts that a variable is not empty.
     *
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     */
    final public static function assert_not_empty(mixed $actual, string $message = ''): void
    {
        if ($actual instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$actual');
        }
        self::assert_that($actual, self::logical_not(self::is_empty()), $message);
    }
    /**
     * Asserts that a value is greater than another value.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_greater_than(mixed $minimum, mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::greater_than($minimum), $message);
    }
    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_greater_than_or_equal(mixed $minimum, mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::greater_than_or_equal($minimum), $message);
    }
    /**
     * Asserts that a value is smaller than another value.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_less_than(mixed $maximum, mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::less_than($maximum), $message);
    }
    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_less_than_or_equal(mixed $maximum, mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::less_than_or_equal($maximum), $message);
    }
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_equals(string $expected, string $actual, string $message = ''): void
    {
        self::assert_file_exists($expected, $message);
        self::assert_file_exists($actual, $message);
        $constraint = new Is_Equal(file_get_contents($expected));
        self::assert_that(file_get_contents($actual), $constraint, $message);
    }
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (canonicalizing).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_equals_canonicalizing(string $expected, string $actual, string $message = ''): void
    {
        self::assert_file_exists($expected, $message);
        self::assert_file_exists($actual, $message);
        $constraint = new Is_Equal_Canonicalizing(file_get_contents($expected));
        self::assert_that(file_get_contents($actual), $constraint, $message);
    }
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (ignoring case).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_equals_ignoring_case(string $expected, string $actual, string $message = ''): void
    {
        self::assert_file_exists($expected, $message);
        self::assert_file_exists($actual, $message);
        $constraint = new Is_Equal_Ignoring_Case(file_get_contents($expected));
        self::assert_that(file_get_contents($actual), $constraint, $message);
    }
    /**
     * Asserts that the contents of one file is not equal to the contents of
     * another file.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_not_equals(string $expected, string $actual, string $message = ''): void
    {
        self::assert_file_exists($expected, $message);
        self::assert_file_exists($actual, $message);
        $constraint = new Logical_Not(new Is_Equal(file_get_contents($expected)));
        self::assert_that(file_get_contents($actual), $constraint, $message);
    }
    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (canonicalizing).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_not_equals_canonicalizing(string $expected, string $actual, string $message = ''): void
    {
        self::assert_file_exists($expected, $message);
        self::assert_file_exists($actual, $message);
        $constraint = new Logical_Not(new Is_Equal_Canonicalizing(file_get_contents($expected)));
        self::assert_that(file_get_contents($actual), $constraint, $message);
    }
    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (ignoring case).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_not_equals_ignoring_case(string $expected, string $actual, string $message = ''): void
    {
        self::assert_file_exists($expected, $message);
        self::assert_file_exists($actual, $message);
        $constraint = new Logical_Not(new Is_Equal_Ignoring_Case(file_get_contents($expected)));
        self::assert_that(file_get_contents($actual), $constraint, $message);
    }
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_equals_file(string $expected_file, string $actual_string, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $constraint = new Is_Equal(file_get_contents($expected_file));
        self::assert_that($actual_string, $constraint, $message);
    }
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (canonicalizing).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_equals_file_canonicalizing(string $expected_file, string $actual_string, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $constraint = new Is_Equal_Canonicalizing(file_get_contents($expected_file));
        self::assert_that($actual_string, $constraint, $message);
    }
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (ignoring case).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_equals_file_ignoring_case(string $expected_file, string $actual_string, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $constraint = new Is_Equal_Ignoring_Case(file_get_contents($expected_file));
        self::assert_that($actual_string, $constraint, $message);
    }
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_not_equals_file(string $expected_file, string $actual_string, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $constraint = new Logical_Not(new Is_Equal(file_get_contents($expected_file)));
        self::assert_that($actual_string, $constraint, $message);
    }
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (canonicalizing).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_not_equals_file_canonicalizing(string $expected_file, string $actual_string, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $constraint = new Logical_Not(new Is_Equal_Canonicalizing(file_get_contents($expected_file)));
        self::assert_that($actual_string, $constraint, $message);
    }
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (ignoring case).
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_not_equals_file_ignoring_case(string $expected_file, string $actual_string, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $constraint = new Logical_Not(new Is_Equal_Ignoring_Case(file_get_contents($expected_file)));
        self::assert_that($actual_string, $constraint, $message);
    }
    /**
     * Asserts that a file/dir is readable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_is_readable(string $filename, string $message = ''): void
    {
        self::assert_that($filename, new Is_Readable(), $message);
    }
    /**
     * Asserts that a file/dir exists and is not readable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_is_not_readable(string $filename, string $message = ''): void
    {
        self::assert_that($filename, new Logical_Not(new Is_Readable()), $message);
    }
    /**
     * Asserts that a file/dir exists and is writable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_is_writable(string $filename, string $message = ''): void
    {
        self::assert_that($filename, new Is_Writable(), $message);
    }
    /**
     * Asserts that a file/dir exists and is not writable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_is_not_writable(string $filename, string $message = ''): void
    {
        self::assert_that($filename, new Logical_Not(new Is_Writable()), $message);
    }
    /**
     * Asserts that a directory exists.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_directory_exists(string $directory, string $message = ''): void
    {
        self::assert_that($directory, new Directory_Exists(), $message);
    }
    /**
     * Asserts that a directory does not exist.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_directory_does_not_exist(string $directory, string $message = ''): void
    {
        self::assert_that($directory, new Logical_Not(new Directory_Exists()), $message);
    }
    /**
     * Asserts that a directory exists and is readable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_directory_is_readable(string $directory, string $message = ''): void
    {
        self::assert_directory_exists($directory, $message);
        self::assert_is_readable($directory, $message);
    }
    /**
     * Asserts that a directory exists and is not readable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_directory_is_not_readable(string $directory, string $message = ''): void
    {
        self::assert_directory_exists($directory, $message);
        self::assert_is_not_readable($directory, $message);
    }
    /**
     * Asserts that a directory exists and is writable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_directory_is_writable(string $directory, string $message = ''): void
    {
        self::assert_directory_exists($directory, $message);
        self::assert_is_writable($directory, $message);
    }
    /**
     * Asserts that a directory exists and is not writable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_directory_is_not_writable(string $directory, string $message = ''): void
    {
        self::assert_directory_exists($directory, $message);
        self::assert_is_not_writable($directory, $message);
    }
    /**
     * Asserts that a file exists.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_exists(string $filename, string $message = ''): void
    {
        self::assert_that($filename, new File_Exists(), $message);
    }
    /**
     * Asserts that a file does not exist.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_does_not_exist(string $filename, string $message = ''): void
    {
        self::assert_that($filename, new Logical_Not(new File_Exists()), $message);
    }
    /**
     * Asserts that a file exists and is readable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_is_readable(string $file, string $message = ''): void
    {
        self::assert_file_exists($file, $message);
        self::assert_is_readable($file, $message);
    }
    /**
     * Asserts that a file exists and is not readable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_is_not_readable(string $file, string $message = ''): void
    {
        self::assert_file_exists($file, $message);
        self::assert_is_not_readable($file, $message);
    }
    /**
     * Asserts that a file exists and is writable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_is_writable(string $file, string $message = ''): void
    {
        self::assert_file_exists($file, $message);
        self::assert_is_writable($file, $message);
    }
    /**
     * Asserts that a file exists and is not writable.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_is_not_writable(string $file, string $message = ''): void
    {
        self::assert_file_exists($file, $message);
        self::assert_is_not_writable($file, $message);
    }
    /**
     * Asserts that a condition is true.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert true $condition
     */
    final public static function assert_true(mixed $condition, string $message = ''): void
    {
        self::assert_that($condition, self::is_true(), $message);
    }
    /**
     * Asserts that a condition is not true.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !true $condition
     */
    final public static function assert_not_true(mixed $condition, string $message = ''): void
    {
        self::assert_that($condition, self::logical_not(self::is_true()), $message);
    }
    /**
     * Asserts that a condition is false.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert false $condition
     */
    final public static function assert_false(mixed $condition, string $message = ''): void
    {
        self::assert_that($condition, self::is_false(), $message);
    }
    /**
     * Asserts that a condition is not false.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !false $condition
     */
    final public static function assert_not_false(mixed $condition, string $message = ''): void
    {
        self::assert_that($condition, self::logical_not(self::is_false()), $message);
    }
    /**
     * Asserts that a variable is null.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert null $actual
     */
    final public static function assert_null(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::is_null(), $message);
    }
    /**
     * Asserts that a variable is not null.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !null $actual
     */
    final public static function assert_not_null(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::logical_not(self::is_null()), $message);
    }
    /**
     * Asserts that a variable is finite.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_finite(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::is_finite(), $message);
    }
    /**
     * Asserts that a variable is infinite.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_infinite(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::is_infinite(), $message);
    }
    /**
     * Asserts that a variable is nan.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_nan(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, self::is_nan(), $message);
    }
    /**
     * Asserts that an object has a specified property.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_object_has_property(string $property_name, object $object, string $message = ''): void
    {
        self::assert_that($object, new Object_Has_Property($property_name), $message);
    }
    /**
     * Asserts that an object does not have a specified property.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_object_not_has_property(string $property_name, object $object, string $message = ''): void
    {
        self::assert_that($object, new Logical_Not(new Object_Has_Property($property_name)), $message);
    }
    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @template ExpectedType
     *
     * @param ExpectedType $expected
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert =ExpectedType $actual
     */
    final public static function assert_same(mixed $expected, mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Identical($expected), $message);
    }
    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_not_same(mixed $expected, mixed $actual, string $message = ''): void
    {
        if (is_bool($expected) && is_bool($actual)) {
            self::assert_not_equals($expected, $actual, $message);
        }
        self::assert_that($actual, new Logical_Not(new Is_Identical($expected)), $message);
    }
    /**
     * Asserts that a variable is of a given type.
     *
     * @template ExpectedType of object
     *
     * @param class-string<ExpectedType> $expected
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws UnknownClassOrInterfaceException
     *
     * @phpstan-assert =ExpectedType $actual
     */
    final public static function assert_instance_of(string $expected, mixed $actual, string $message = ''): void
    {
        if (!class_exists($expected) && !interface_exists($expected)) {
            throw new Unknown_Class_Or_Interface_Exception($expected);
        }
        self::assert_that($actual, new Is_Instance_Of($expected), $message);
    }
    /**
     * Asserts that a variable is not of a given type.
     *
     * @template ExpectedType of object
     *
     * @param class-string<ExpectedType> $expected
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !ExpectedType $actual
     */
    final public static function assert_not_instance_of(string $expected, mixed $actual, string $message = ''): void
    {
        if (!class_exists($expected) && !interface_exists($expected)) {
            throw new Unknown_Class_Or_Interface_Exception($expected);
        }
        self::assert_that($actual, new Logical_Not(new Is_Instance_Of($expected)), $message);
    }
    /**
     * Asserts that a variable is of type array.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert array<mixed> $actual
     */
    final public static function assert_is_array(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Array), $message);
    }
    /**
     * Asserts that a variable is of type bool.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert bool $actual
     */
    final public static function assert_is_bool(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Bool), $message);
    }
    /**
     * Asserts that a variable is of type float.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert float $actual
     */
    final public static function assert_is_float(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Float), $message);
    }
    /**
     * Asserts that a variable is of type int.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert int $actual
     */
    final public static function assert_is_int(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Int), $message);
    }
    /**
     * Asserts that a variable is of type numeric.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert numeric $actual
     */
    final public static function assert_is_numeric(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Numeric), $message);
    }
    /**
     * Asserts that a variable is of type object.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert object $actual
     */
    final public static function assert_is_object(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Object), $message);
    }
    /**
     * Asserts that a variable is of type resource.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert resource $actual
     */
    final public static function assert_is_resource(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Resource), $message);
    }
    /**
     * Asserts that a variable is of type resource and is closed.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert resource $actual
     */
    final public static function assert_is_closed_resource(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::ClosedResource), $message);
    }
    /**
     * Asserts that a variable is of type string.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert string $actual
     */
    final public static function assert_is_string(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::String), $message);
    }
    /**
     * Asserts that a variable is of type scalar.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert scalar $actual
     */
    final public static function assert_is_scalar(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Scalar), $message);
    }
    /**
     * Asserts that a variable is of type callable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert callable $actual
     */
    final public static function assert_is_callable(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Callable), $message);
    }
    /**
     * Asserts that a variable is of type iterable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert iterable<mixed> $actual
     */
    final public static function assert_is_iterable(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Is_Type(Native_Type::Iterable), $message);
    }
    /**
     * Asserts that a variable is not of type array.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !array<mixed> $actual
     */
    final public static function assert_is_not_array(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Array)), $message);
    }
    /**
     * Asserts that a variable is not of type bool.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !bool $actual
     */
    final public static function assert_is_not_bool(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Bool)), $message);
    }
    /**
     * Asserts that a variable is not of type float.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !float $actual
     */
    final public static function assert_is_not_float(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Float)), $message);
    }
    /**
     * Asserts that a variable is not of type int.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !int $actual
     */
    final public static function assert_is_not_int(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Int)), $message);
    }
    /**
     * Asserts that a variable is not of type numeric.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !numeric $actual
     */
    final public static function assert_is_not_numeric(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Numeric)), $message);
    }
    /**
     * Asserts that a variable is not of type object.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !object $actual
     */
    final public static function assert_is_not_object(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Object)), $message);
    }
    /**
     * Asserts that a variable is not of type resource.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !resource $actual
     */
    final public static function assert_is_not_resource(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Resource)), $message);
    }
    /**
     * Asserts that a variable is not of type resource.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !resource $actual
     */
    final public static function assert_is_not_closed_resource(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::ClosedResource)), $message);
    }
    /**
     * Asserts that a variable is not of type string.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !string $actual
     */
    final public static function assert_is_not_string(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::String)), $message);
    }
    /**
     * Asserts that a variable is not of type scalar.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !scalar $actual
     */
    final public static function assert_is_not_scalar(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Scalar)), $message);
    }
    /**
     * Asserts that a variable is not of type callable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !callable $actual
     */
    final public static function assert_is_not_callable(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Callable)), $message);
    }
    /**
     * Asserts that a variable is not of type iterable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !iterable<mixed> $actual
     */
    final public static function assert_is_not_iterable(mixed $actual, string $message = ''): void
    {
        self::assert_that($actual, new Logical_Not(new Is_Type(Native_Type::Iterable)), $message);
    }
    /**
     * Asserts that a string matches a given regular expression.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_matches_regular_expression(string $pattern, string $string, string $message = ''): void
    {
        self::assert_that($string, new Regular_Expression($pattern), $message);
    }
    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_does_not_match_regular_expression(string $pattern, string $string, string $message = ''): void
    {
        self::assert_that($string, new Logical_Not(new Regular_Expression($pattern)), $message);
    }
    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param Countable|iterable<mixed> $expected
     * @param Countable|iterable<mixed> $actual
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     */
    final public static function assert_same_size(Countable|iterable $expected, Countable|iterable $actual, string $message = ''): void
    {
        if ($expected instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$expected');
        }
        if ($actual instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$actual');
        }
        self::assert_that($actual, new Same_Size($expected), $message);
    }
    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is not the same.
     *
     * @param Countable|iterable<mixed> $expected
     * @param Countable|iterable<mixed> $actual
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     */
    final public static function assert_not_same_size(Countable|iterable $expected, Countable|iterable $actual, string $message = ''): void
    {
        if ($expected instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$expected');
        }
        if ($actual instanceof Generator) {
            throw Generator_Not_Supported_Exception::from_parameter_name('$actual');
        }
        self::assert_that($actual, new Logical_Not(new Same_Size($expected)), $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_string_contains_string_ignoring_line_endings(string $needle, string $haystack, string $message = ''): void
    {
        self::assert_that($haystack, new String_Contains($needle, false, true), $message);
    }
    /**
     * Asserts that two strings are equal except for line endings.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_equals_string_ignoring_line_endings(string $expected, string $actual, string $message = ''): void
    {
        self::assert_that($actual, new String_Equals_String_Ignoring_Line_Endings($expected), $message);
    }
    /**
     * Asserts that a string matches a given format string.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_matches_format(string $format, string $actual_file, string $message = ''): void
    {
        self::assert_file_exists($actual_file, $message);
        self::assert_that(file_get_contents($actual_file), new String_Matches_Format_Description($format), $message);
    }
    /**
     * Asserts that a string matches a given format string.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_file_matches_format_file(string $format_file, string $actual_file, string $message = ''): void
    {
        self::assert_file_exists($format_file, $message);
        self::assert_file_exists($actual_file, $message);
        $format_description = file_get_contents($format_file);
        self::assert_is_string($format_description);
        self::assert_that(file_get_contents($actual_file), new String_Matches_Format_Description($format_description), $message);
    }
    /**
     * Asserts that a string matches a given format string.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_matches_format(string $format, string $string, string $message = ''): void
    {
        self::assert_that($string, new String_Matches_Format_Description($format), $message);
    }
    /**
     * Asserts that a string matches a given format file.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_string_matches_format_file(string $format_file, string $string, string $message = ''): void
    {
        self::assert_file_exists($format_file, $message);
        $format_description = file_get_contents($format_file);
        self::assert_is_string($format_description);
        self::assert_that($string, new String_Matches_Format_Description($format_description), $message);
    }
    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param non-empty-string $prefix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    final public static function assert_string_starts_with(string $prefix, string $string, string $message = ''): void
    {
        self::assert_that($string, new String_Starts_With($prefix), $message);
    }
    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param non-empty-string $prefix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    final public static function assert_string_starts_not_with(string $prefix, string $string, string $message = ''): void
    {
        self::assert_that($string, new Logical_Not(new String_Starts_With($prefix)), $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_string_contains_string(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new String_Contains($needle);
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_string_contains_string_ignoring_case(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new String_Contains($needle, true);
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_string_not_contains_string(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new Logical_Not(new String_Contains($needle));
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * @throws ExpectationFailedException
     */
    final public static function assert_string_not_contains_string_ignoring_case(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new Logical_Not(new String_Contains($needle, true));
        self::assert_that($haystack, $constraint, $message);
    }
    /**
     * Asserts that a string ends with a given suffix.
     *
     * @param non-empty-string $suffix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    final public static function assert_string_ends_with(string $suffix, string $string, string $message = ''): void
    {
        self::assert_that($string, new String_Ends_With($suffix), $message);
    }
    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @param non-empty-string $suffix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    final public static function assert_string_ends_not_with(string $suffix, string $string, string $message = ''): void
    {
        self::assert_that($string, new Logical_Not(new String_Ends_With($suffix)), $message);
    }
    /**
     * Asserts that two XML files are equal.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws XmlException
     */
    final public static function assert_xml_file_equals_xml_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        $expected = (new Xml_Loader())->load_file($expected_file);
        $actual = (new Xml_Loader())->load_file($actual_file);
        self::assert_equals($expected, $actual, $message);
    }
    /**
     * Asserts that two XML files are not equal.
     *
     * @throws \PHPUnit\Util\Exception
     * @throws ExpectationFailedException
     */
    final public static function assert_xml_file_not_equals_xml_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        $expected = (new Xml_Loader())->load_file($expected_file);
        $actual = (new Xml_Loader())->load_file($actual_file);
        self::assert_not_equals($expected, $actual, $message);
    }
    /**
     * Asserts that two XML documents are equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     */
    final public static function assert_xml_string_equals_xml_file(string $expected_file, string $actual_xml, string $message = ''): void
    {
        $expected = (new Xml_Loader())->load_file($expected_file);
        $actual = (new Xml_Loader())->load($actual_xml);
        self::assert_equals($expected, $actual, $message);
    }
    /**
     * Asserts that two XML documents are not equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     */
    final public static function assert_xml_string_not_equals_xml_file(string $expected_file, string $actual_xml, string $message = ''): void
    {
        $expected = (new Xml_Loader())->load_file($expected_file);
        $actual = (new Xml_Loader())->load($actual_xml);
        self::assert_not_equals($expected, $actual, $message);
    }
    /**
     * Asserts that two XML documents are equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     */
    final public static function assert_xml_string_equals_xml_string(string $expected_xml, string $actual_xml, string $message = ''): void
    {
        $expected = (new Xml_Loader())->load($expected_xml);
        $actual = (new Xml_Loader())->load($actual_xml);
        self::assert_equals($expected, $actual, $message);
    }
    /**
     * Asserts that two XML documents are not equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     */
    final public static function assert_xml_string_not_equals_xml_string(string $expected_xml, string $actual_xml, string $message = ''): void
    {
        $expected = (new Xml_Loader())->load($expected_xml);
        $actual = (new Xml_Loader())->load($actual_xml);
        self::assert_not_equals($expected, $actual, $message);
    }
    /**
     * Evaluates a Constraint matcher object against a value.
     *
     * This is the lowest-level assertion method. All other assertion methods
     * ultimately delegate to this one. Use it directly when you have a custom
     * {@see Constraint} object or need to compose constraints with
     * {@see logicalAnd()}, {@see logicalOr()}, or {@see logicalNot()}.
     *
     * Example:
     *   self::assertThat($value, self::logicalAnd(
     *       self::greaterThan(0),
     *       self::lessThan(100),
     *   ));
     *
     * @param mixed      $value      The value to evaluate the constraint against
     * @param Constraint $constraint The constraint to evaluate
     * @param string     $message    Optional failure message prepended to the generated message
     *
     * @throws ExpectationFailedException when the constraint is not satisfied
     */
    final public static function assert_that(mixed $value, Constraint $constraint, string $message = ''): void
    {
        self::$count += count($constraint);
        $constraint->evaluate($value, $message);
    }
    /**
     * Asserts that a string is a valid JSON string.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json(string $actual, string $message = ''): void
    {
        self::assert_that($actual, self::is_json(), $message);
    }
    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json_string_equals_json_string(string $expected_json, string $actual_json, string $message = ''): void
    {
        self::assert_json($expected_json, $message);
        self::assert_json($actual_json, $message);
        self::assert_that($actual_json, new Json_Matches($expected_json), $message);
    }
    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json_string_not_equals_json_string(string $expected_json, string $actual_json, string $message = ''): void
    {
        self::assert_json($expected_json, $message);
        self::assert_json($actual_json, $message);
        self::assert_that($actual_json, new Logical_Not(new Json_Matches($expected_json)), $message);
    }
    /**
     * Asserts that the generated JSON encoded object and the content of the given file are equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json_string_equals_json_file(string $expected_file, string $actual_json, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $expected_json = file_get_contents($expected_file);
        self::assert_is_string($expected_json);
        self::assert_json($expected_json, $message);
        self::assert_json($actual_json, $message);
        self::assert_that($actual_json, new Json_Matches($expected_json), $message);
    }
    /**
     * Asserts that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json_string_not_equals_json_file(string $expected_file, string $actual_json, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $expected_json = file_get_contents($expected_file);
        self::assert_is_string($expected_json);
        self::assert_json($expected_json, $message);
        self::assert_json($actual_json, $message);
        self::assert_that($actual_json, new Logical_Not(new Json_Matches($expected_json)), $message);
    }
    /**
     * Asserts that two JSON files are equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json_file_equals_json_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $expected_json = file_get_contents($expected_file);
        self::assert_is_string($expected_json);
        self::assert_json($expected_json, $message);
        self::assert_file_exists($actual_file, $message);
        $actual_json = file_get_contents($actual_file);
        self::assert_is_string($actual_json);
        self::assert_json($actual_json, $message);
        self::assert_that($actual_json, new Json_Matches($expected_json), $message);
    }
    /**
     * Asserts that two JSON files are not equal.
     *
     * @throws ExpectationFailedException
     */
    final public static function assert_json_file_not_equals_json_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        self::assert_file_exists($expected_file, $message);
        $expected_json = file_get_contents($expected_file);
        self::assert_is_string($expected_json);
        self::assert_json($expected_json, $message);
        self::assert_file_exists($actual_file, $message);
        $actual_json = file_get_contents($actual_file);
        self::assert_is_string($actual_json);
        self::assert_json($actual_json, $message);
        self::assert_that($actual_json, self::logical_not(new Json_Matches($expected_json)), $message);
    }
    /**
     * @throws Exception
     */
    final public static function logical_and(mixed ...$constraints): Logical_And
    {
        return Logical_And::from_constraints(...$constraints);
    }
    final public static function logical_or(mixed ...$constraints): Logical_Or
    {
        return Logical_Or::from_constraints(...$constraints);
    }
    final public static function logical_not(Constraint $constraint): Logical_Not
    {
        return new Logical_Not($constraint);
    }
    final public static function logical_xor(mixed ...$constraints): Logical_Xor
    {
        return Logical_Xor::from_constraints(...$constraints);
    }
    final public static function anything(): Is_Anything
    {
        return new Is_Anything();
    }
    final public static function is_true(): Is_True
    {
        return new Is_True();
    }
    /**
     * @template CallbackInput of mixed
     *
     * @param callable(CallbackInput $callback): bool $callback
     *
     * @return Callback<CallbackInput>
     */
    final public static function callback(callable $callback): Callback
    {
        return new Callback($callback);
    }
    final public static function is_false(): Is_False
    {
        return new Is_False();
    }
    final public static function is_json(): Is_Json
    {
        return new Is_Json();
    }
    final public static function is_null(): Is_Null
    {
        return new Is_Null();
    }
    final public static function is_finite(): Is_Finite
    {
        return new Is_Finite();
    }
    final public static function is_infinite(): Is_Infinite
    {
        return new Is_Infinite();
    }
    final public static function is_nan(): Is_Nan
    {
        return new Is_Nan();
    }
    final public static function contains_equal(mixed $value): Traversable_Contains_Equal
    {
        return new Traversable_Contains_Equal($value);
    }
    final public static function contains_identical(mixed $value): Traversable_Contains_Identical
    {
        return new Traversable_Contains_Identical($value);
    }
    final public static function contains_only_array(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Array);
    }
    final public static function contains_only_bool(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Bool);
    }
    final public static function contains_only_callable(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Callable);
    }
    final public static function contains_only_float(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Float);
    }
    final public static function contains_only_int(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Int);
    }
    final public static function contains_only_iterable(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Iterable);
    }
    final public static function contains_only_null(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Null);
    }
    final public static function contains_only_numeric(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Numeric);
    }
    final public static function contains_only_object(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Object);
    }
    final public static function contains_only_resource(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Resource);
    }
    final public static function contains_only_closed_resource(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::ClosedResource);
    }
    final public static function contains_only_scalar(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::Scalar);
    }
    final public static function contains_only_string(): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_native_type(Native_Type::String);
    }
    /**
     * @param class-string $className
     *
     * @throws Exception
     */
    final public static function contains_only_instances_of(string $class_name): Traversable_Contains_Only
    {
        return Traversable_Contains_Only::for_class_or_interface($class_name);
    }
    final public static function array_has_key(mixed $key): Array_Has_Key
    {
        return new Array_Has_Key($key);
    }
    final public static function is_list(): Is_List
    {
        return new Is_List();
    }
    final public static function equal_to(mixed $value): Is_Equal
    {
        return new Is_Equal($value);
    }
    final public static function equal_to_canonicalizing(mixed $value): Is_Equal_Canonicalizing
    {
        return new Is_Equal_Canonicalizing($value);
    }
    final public static function equal_to_ignoring_case(mixed $value): Is_Equal_Ignoring_Case
    {
        return new Is_Equal_Ignoring_Case($value);
    }
    final public static function equal_to_with_delta(mixed $value, float $delta): Is_Equal_With_Delta
    {
        return new Is_Equal_With_Delta($value, $delta);
    }
    final public static function is_empty(): Is_Empty
    {
        return new Is_Empty();
    }
    final public static function is_writable(): Is_Writable
    {
        return new Is_Writable();
    }
    final public static function is_readable(): Is_Readable
    {
        return new Is_Readable();
    }
    final public static function directory_exists(): Directory_Exists
    {
        return new Directory_Exists();
    }
    final public static function file_exists(): File_Exists
    {
        return new File_Exists();
    }
    final public static function greater_than(mixed $value): Greater_Than
    {
        return new Greater_Than($value);
    }
    final public static function greater_than_or_equal(mixed $value): Logical_Or
    {
        return self::logical_or(new Is_Equal($value), new Greater_Than($value));
    }
    final public static function identical_to(mixed $value): Is_Identical
    {
        return new Is_Identical($value);
    }
    /**
     * @throws UnknownClassOrInterfaceException
     */
    final public static function is_instance_of(string $class_name): Is_Instance_Of
    {
        return new Is_Instance_Of($class_name);
    }
    final public static function is_array(): Is_Type
    {
        return new Is_Type(Native_Type::Array);
    }
    final public static function is_bool(): Is_Type
    {
        return new Is_Type(Native_Type::Bool);
    }
    final public static function is_callable(): Is_Type
    {
        return new Is_Type(Native_Type::Callable);
    }
    final public static function is_float(): Is_Type
    {
        return new Is_Type(Native_Type::Float);
    }
    final public static function is_int(): Is_Type
    {
        return new Is_Type(Native_Type::Int);
    }
    final public static function is_iterable(): Is_Type
    {
        return new Is_Type(Native_Type::Iterable);
    }
    final public static function is_numeric(): Is_Type
    {
        return new Is_Type(Native_Type::Numeric);
    }
    final public static function is_object(): Is_Type
    {
        return new Is_Type(Native_Type::Object);
    }
    final public static function is_resource(): Is_Type
    {
        return new Is_Type(Native_Type::Resource);
    }
    final public static function is_closed_resource(): Is_Type
    {
        return new Is_Type(Native_Type::ClosedResource);
    }
    final public static function is_scalar(): Is_Type
    {
        return new Is_Type(Native_Type::Scalar);
    }
    final public static function is_string(): Is_Type
    {
        return new Is_Type(Native_Type::String);
    }
    final public static function less_than(mixed $value): Less_Than
    {
        return new Less_Than($value);
    }
    final public static function less_than_or_equal(mixed $value): Logical_Or
    {
        return self::logical_or(new Is_Equal($value), new Less_Than($value));
    }
    final public static function matches_regular_expression(string $pattern): Regular_Expression
    {
        return new Regular_Expression($pattern);
    }
    final public static function matches(string $string): String_Matches_Format_Description
    {
        return new String_Matches_Format_Description($string);
    }
    /**
     * @param non-empty-string $prefix
     *
     * @throws InvalidArgumentException
     */
    final public static function string_starts_with(string $prefix): String_Starts_With
    {
        return new String_Starts_With($prefix);
    }
    final public static function string_contains(string $string, bool $case = true): String_Contains
    {
        return new String_Contains($string, $case);
    }
    /**
     * @param non-empty-string $suffix
     *
     * @throws InvalidArgumentException
     */
    final public static function string_ends_with(string $suffix): String_Ends_With
    {
        return new String_Ends_With($suffix);
    }
    final public static function string_equals_string_ignoring_line_endings(string $string): String_Equals_String_Ignoring_Line_Endings
    {
        return new String_Equals_String_Ignoring_Line_Endings($string);
    }
    final public static function count_of(int $count): Count
    {
        return new Count($count);
    }
    final public static function object_equals(object $object, string $method = 'equals'): Object_Equals
    {
        return new Object_Equals($object, $method);
    }
    /**
     * Fails a test with the given message.
     *
     * @throws AssertionFailedError
     */
    final public static function fail(string $message = ''): never
    {
        self::$count++;
        throw new Assertion_Failed_Error($message);
    }
    /**
     * Mark the test as incomplete.
     *
     * @throws IncompleteTestError
     */
    final public static function mark_test_incomplete(string $message = ''): never
    {
        throw new Incomplete_Test_Error($message);
    }
    /**
     * Mark the test as skipped.
     *
     * @throws SkippedWithMessageException
     */
    final public static function mark_test_skipped(string $message = ''): never
    {
        throw new Skipped_With_Message_Exception($message);
    }
    /**
     * Return the current assertion count.
     */
    final public static function get_count(): int
    {
        return self::$count;
    }
    /**
     * Reset the assertion counter.
     */
    final public static function reset_count(): void
    {
        self::$count = 0;
    }
}