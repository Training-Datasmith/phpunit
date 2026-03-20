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

use ArrayAccess;
use Countable;
use function func_get_args;
use function function_exists;
use const PHP_EOL;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Constraint\Array_Has_Key;
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
use Php_Unit\Framework\Constraint\Less_Than;
use Php_Unit\Framework\Constraint\Logical_And;
use Php_Unit\Framework\Constraint\Logical_Not;
use Php_Unit\Framework\Constraint\Logical_Or;
use Php_Unit\Framework\Constraint\Logical_Xor;
use Php_Unit\Framework\Constraint\Object_Equals;
use Php_Unit\Framework\Constraint\Regular_Expression;
use Php_Unit\Framework\Constraint\String_Contains;
use Php_Unit\Framework\Constraint\String_Ends_With;
use Php_Unit\Framework\Constraint\String_Equals_String_Ignoring_Line_Endings;
use Php_Unit\Framework\Constraint\String_Matches_Format_Description;
use Php_Unit\Framework\Constraint\String_Starts_With;
use Php_Unit\Framework\Constraint\Traversable_Contains_Equal;
use Php_Unit\Framework\Constraint\Traversable_Contains_Identical;
use Php_Unit\Framework\Constraint\Traversable_Contains_Only;
use Php_Unit\Framework\Mock_Object\Rule\Any_Invoked_Count as AnyInvokedCountMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Least_Count as InvokedAtLeastCountMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Least_Once as InvokedAtLeastOnceMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_At_Most_Count as InvokedAtMostCountMatcher;
use Php_Unit\Framework\Mock_Object\Rule\Invoked_Count as InvokedCountMatcher;
use Php_Unit\Framework\Mock_Object\Stub\Exception as ExceptionStub;
use Php_Unit\Util\Xml\Xml_Exception;
use Throwable;
if (!function_exists('PHPUnit\Framework\assertArrayIsEqualToArrayOnlyConsideringListOfKeys')) {
    /**
     * Asserts that two arrays are equal while only considering a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeConsidered
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArrayIsEqualToArrayOnlyConsideringListOfKeys
     */
    function assert_array_is_equal_to_array_only_considering_list_of_keys(array $expected, array $actual, array $keys_to_be_considered, string $message = ''): void
    {
        Assert::assert_array_is_equal_to_array_only_considering_list_of_keys(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArrayIsEqualToArrayIgnoringListOfKeys')) {
    /**
     * Asserts that two arrays are equal while ignoring a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeIgnored
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArrayIsEqualToArrayIgnoringListOfKeys
     */
    function assert_array_is_equal_to_array_ignoring_list_of_keys(array $expected, array $actual, array $keys_to_be_ignored, string $message = ''): void
    {
        Assert::assert_array_is_equal_to_array_ignoring_list_of_keys(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys')) {
    /**
     * Asserts that two arrays are identical while only considering a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeConsidered
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys
     */
    function assert_array_is_identical_to_array_only_considering_list_of_keys(array $expected, array $actual, array $keys_to_be_considered, string $message = ''): void
    {
        Assert::assert_array_is_identical_to_array_only_considering_list_of_keys(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArrayIsIdenticalToArrayIgnoringListOfKeys')) {
    /**
     * Asserts that two arrays are equal while ignoring a list of keys.
     *
     * @param array<mixed>              $expected
     * @param array<mixed>              $actual
     * @param non-empty-list<array-key> $keysToBeIgnored
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArrayIsIdenticalToArrayIgnoringListOfKeys
     */
    function assert_array_is_identical_to_array_ignoring_list_of_keys(array $expected, array $actual, array $keys_to_be_ignored, string $message = ''): void
    {
        Assert::assert_array_is_identical_to_array_ignoring_list_of_keys(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArrayHasKey')) {
    /**
     * Asserts that an array has a specified key.
     *
     * @param array<mixed>|ArrayAccess<array-key, mixed> $array
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArrayHasKey
     */
    function assert_array_has_key(mixed $key, array|ArrayAccess $array, string $message = ''): void
    {
        Assert::assert_array_has_key(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArrayNotHasKey')) {
    /**
     * Asserts that an array does not have a specified key.
     *
     * @param array<mixed>|ArrayAccess<array-key, mixed> $array
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArrayNotHasKey
     */
    function assert_array_not_has_key(mixed $key, array|ArrayAccess $array, string $message = ''): void
    {
        Assert::assert_array_not_has_key(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsList')) {
    /**
     * @phpstan-assert list<mixed> $array
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsList
     */
    function assert_is_list(mixed $array, string $message = ''): void
    {
        Assert::assert_is_list(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysAreIdentical')) {
    /**
     * Assert that two arrays are identical.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array matters, and keys as well as values are compared strictly.
     * This is essentially an alias for assertSame().
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysAreIdentical
     */
    function assert_arrays_are_identical(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_are_identical(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysAreEqual')) {
    /**
     * Assert that two arrays are equal.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array matters, and keys as well as values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysAreEqual
     */
    function assert_arrays_are_equal(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_are_equal(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysAreIdenticalIgnoringOrder')) {
    /**
     * Assert that two arrays are identical while ignoring the order of their values.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array does not matter, and keys as well as values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysAreIdenticalIgnoringOrder
     */
    function assert_arrays_are_identical_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_are_identical_ignoring_order(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysAreEqualIgnoringOrder')) {
    /**
     * Assert that two arrays are equal while ignoring the order of their values.
     *
     * The (key, value) relationship matters, the order of the (key, value) pairs in the array does not matter, and keys as well as values are compared loosely.
     * This is essentially an alias for assertEquals().
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysAreEqualIgnoringOrder
     */
    function assert_arrays_are_equal_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_are_equal_ignoring_order(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysHaveIdenticalValues')) {
    /**
     * Assert that two arrays have identical values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array matters, and values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysHaveIdenticalValues
     */
    function assert_arrays_have_identical_values(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_have_identical_values(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysHaveEqualValues')) {
    /**
     * Assert that two arrays have equal values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array matters, and values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysHaveEqualValues
     */
    function assert_arrays_have_equal_values(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_have_equal_values(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysHaveIdenticalValuesIgnoringOrder')) {
    /**
     * Assert that two arrays have identical values while ignoring the order of these values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array does not matter, and values are compared strictly.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysHaveIdenticalValuesIgnoringOrder
     */
    function assert_arrays_have_identical_values_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_have_identical_values_ignoring_order(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertArraysHaveEqualValuesIgnoringOrder')) {
    /**
     * Assert that two arrays have equal values while ignoring the order of these values.
     *
     * The (key, value) relationship does not matter, the order of the (key, value) pairs in the array does not matter, and values are compared loosely.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertArraysHaveEqualValuesIgnoringOrder
     */
    function assert_arrays_have_equal_values_ignoring_order(array $expected, array $actual, string $message = ''): void
    {
        Assert::assert_arrays_have_equal_values_ignoring_order(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContains')) {
    /**
     * Asserts that a haystack contains a needle.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContains
     */
    function assert_contains(mixed $needle, iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsEquals')) {
    /**
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsEquals
     */
    function assert_contains_equals(mixed $needle, iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotContains')) {
    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotContains
     */
    function assert_not_contains(mixed $needle, iterable $haystack, string $message = ''): void
    {
        Assert::assert_not_contains(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotContainsEquals')) {
    /**
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotContainsEquals
     */
    function assert_not_contains_equals(mixed $needle, iterable $haystack, string $message = ''): void
    {
        Assert::assert_not_contains_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyArray')) {
    /**
     * Asserts that a haystack contains only values of type array.
     *
     * @phpstan-assert iterable<array<mixed>> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyArray
     */
    function assert_contains_only_array(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_array(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyBool')) {
    /**
     * Asserts that a haystack contains only values of type bool.
     *
     * @phpstan-assert iterable<bool> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyBool
     */
    function assert_contains_only_bool(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_bool(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyCallable')) {
    /**
     * Asserts that a haystack contains only values of type callable.
     *
     * @phpstan-assert iterable<callable> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyCallable
     */
    function assert_contains_only_callable(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_callable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyFloat')) {
    /**
     * Asserts that a haystack contains only values of type float.
     *
     * @phpstan-assert iterable<float> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyFloat
     */
    function assert_contains_only_float(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_float(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyInt')) {
    /**
     * Asserts that a haystack contains only values of type int.
     *
     * @phpstan-assert iterable<int> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyInt
     */
    function assert_contains_only_int(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_int(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyIterable')) {
    /**
     * Asserts that a haystack contains only values of type iterable.
     *
     * @phpstan-assert iterable<iterable<mixed>> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyIterable
     */
    function assert_contains_only_iterable(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_iterable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyNull')) {
    /**
     * Asserts that a haystack contains only values of type null.
     *
     * @phpstan-assert iterable<null> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyNull
     */
    function assert_contains_only_null(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_null(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyNumeric')) {
    /**
     * Asserts that a haystack contains only values of type numeric.
     *
     * @phpstan-assert iterable<numeric> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyNumeric
     */
    function assert_contains_only_numeric(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_numeric(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyObject')) {
    /**
     * Asserts that a haystack contains only values of type object.
     *
     * @phpstan-assert iterable<object> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyObject
     */
    function assert_contains_only_object(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_object(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyResource')) {
    /**
     * Asserts that a haystack contains only values of type resource.
     *
     * @phpstan-assert iterable<resource> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyResource
     */
    function assert_contains_only_resource(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyClosedResource')) {
    /**
     * Asserts that a haystack contains only values of type closed resource.
     *
     * @phpstan-assert iterable<resource> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyClosedResource
     */
    function assert_contains_only_closed_resource(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_closed_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyScalar')) {
    /**
     * Asserts that a haystack contains only values of type scalar.
     *
     * @phpstan-assert iterable<scalar> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyScalar
     */
    function assert_contains_only_scalar(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_scalar(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyString')) {
    /**
     * Asserts that a haystack contains only values of type string.
     *
     * @phpstan-assert iterable<string> $haystack
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyString
     */
    function assert_contains_only_string(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsOnlyInstancesOf')) {
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
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsOnlyInstancesOf
     */
    function assert_contains_only_instances_of(string $class_name, iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_only_instances_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyArray')) {
    /**
     * Asserts that a haystack does not contain only values of type array.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyArray
     */
    function assert_contains_not_only_array(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_array(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyBool')) {
    /**
     * Asserts that a haystack does not contain only values of type bool.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyBool
     */
    function assert_contains_not_only_bool(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_bool(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyCallable')) {
    /**
     * Asserts that a haystack does not contain only values of type callable.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyCallable
     */
    function assert_contains_not_only_callable(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_callable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyFloat')) {
    /**
     * Asserts that a haystack does not contain only values of type float.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyFloat
     */
    function assert_contains_not_only_float(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_float(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyInt')) {
    /**
     * Asserts that a haystack does not contain only values of type int.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyInt
     */
    function assert_contains_not_only_int(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_int(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyIterable')) {
    /**
     * Asserts that a haystack does not contain only values of type iterable.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyIterable
     */
    function assert_contains_not_only_iterable(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_iterable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyNull')) {
    /**
     * Asserts that a haystack does not contain only values of type null.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyNull
     */
    function assert_contains_not_only_null(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_null(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyNumeric')) {
    /**
     * Asserts that a haystack does not contain only values of type numeric.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyNumeric
     */
    function assert_contains_not_only_numeric(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_numeric(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyObject')) {
    /**
     * Asserts that a haystack does not contain only values of type object.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyObject
     */
    function assert_contains_not_only_object(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_object(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyResource')) {
    /**
     * Asserts that a haystack does not contain only values of type resource.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyResource
     */
    function assert_contains_not_only_resource(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyClosedResource')) {
    /**
     * Asserts that a haystack does not contain only values of type closed resource.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyClosedResource
     */
    function assert_contains_not_only_closed_resource(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_closed_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyScalar')) {
    /**
     * Asserts that a haystack does not contain only values of type scalar.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyScalar
     */
    function assert_contains_not_only_scalar(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_scalar(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyString')) {
    /**
     * Asserts that a haystack does not contain only values of type string.
     *
     * @param iterable<mixed> $haystack
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyString
     */
    function assert_contains_not_only_string(iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertContainsNotOnlyInstancesOf')) {
    /**
     * Asserts that a haystack does not contain only instances of a specified interface or class name.
     *
     * @param class-string    $className
     * @param iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertContainsNotOnlyInstancesOf
     */
    function assert_contains_not_only_instances_of(string $class_name, iterable $haystack, string $message = ''): void
    {
        Assert::assert_contains_not_only_instances_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertCount')) {
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param Countable|iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertCount
     */
    function assert_count(int $expected_count, Countable|iterable $haystack, string $message = ''): void
    {
        Assert::assert_count(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotCount')) {
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param Countable|iterable<mixed> $haystack
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotCount
     */
    function assert_not_count(int $expected_count, Countable|iterable $haystack, string $message = ''): void
    {
        Assert::assert_not_count(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertEquals')) {
    /**
     * Asserts that two variables are equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertEquals
     */
    function assert_equals(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertEqualsCanonicalizing')) {
    /**
     * Asserts that two variables are equal (canonicalizing).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertEqualsCanonicalizing
     */
    function assert_equals_canonicalizing(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_equals_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertEqualsIgnoringCase')) {
    /**
     * Asserts that two variables are equal (ignoring case).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertEqualsIgnoringCase
     */
    function assert_equals_ignoring_case(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_equals_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertEqualsWithDelta')) {
    /**
     * Asserts that two variables are equal (with delta).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertEqualsWithDelta
     */
    function assert_equals_with_delta(mixed $expected, mixed $actual, float $delta, string $message = ''): void
    {
        Assert::assert_equals_with_delta(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotEquals')) {
    /**
     * Asserts that two variables are not equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotEquals
     */
    function assert_not_equals(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_not_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotEqualsCanonicalizing')) {
    /**
     * Asserts that two variables are not equal (canonicalizing).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotEqualsCanonicalizing
     */
    function assert_not_equals_canonicalizing(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_not_equals_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotEqualsIgnoringCase')) {
    /**
     * Asserts that two variables are not equal (ignoring case).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotEqualsIgnoringCase
     */
    function assert_not_equals_ignoring_case(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_not_equals_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotEqualsWithDelta')) {
    /**
     * Asserts that two variables are not equal (with delta).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotEqualsWithDelta
     */
    function assert_not_equals_with_delta(mixed $expected, mixed $actual, float $delta, string $message = ''): void
    {
        Assert::assert_not_equals_with_delta(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertObjectEquals')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertObjectEquals
     */
    function assert_object_equals(object $expected, object $actual, string $method = 'equals', string $message = ''): void
    {
        Assert::assert_object_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertObjectNotEquals')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertObjectNotEquals
     */
    function assert_object_not_equals(object $expected, object $actual, string $method = 'equals', string $message = ''): void
    {
        Assert::assert_object_not_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertEmpty')) {
    /**
     * Asserts that a variable is empty.
     *
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertEmpty
     */
    function assert_empty(mixed $actual, string $message = ''): void
    {
        Assert::assert_empty(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotEmpty')) {
    /**
     * Asserts that a variable is not empty.
     *
     * @throws ExpectationFailedException
     * @throws GeneratorNotSupportedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotEmpty
     */
    function assert_not_empty(mixed $actual, string $message = ''): void
    {
        Assert::assert_not_empty(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertGreaterThan')) {
    /**
     * Asserts that a value is greater than another value.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertGreaterThan
     */
    function assert_greater_than(mixed $minimum, mixed $actual, string $message = ''): void
    {
        Assert::assert_greater_than(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertGreaterThanOrEqual')) {
    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertGreaterThanOrEqual
     */
    function assert_greater_than_or_equal(mixed $minimum, mixed $actual, string $message = ''): void
    {
        Assert::assert_greater_than_or_equal(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertLessThan')) {
    /**
     * Asserts that a value is smaller than another value.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertLessThan
     */
    function assert_less_than(mixed $maximum, mixed $actual, string $message = ''): void
    {
        Assert::assert_less_than(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertLessThanOrEqual')) {
    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertLessThanOrEqual
     */
    function assert_less_than_or_equal(mixed $maximum, mixed $actual, string $message = ''): void
    {
        Assert::assert_less_than_or_equal(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileEquals')) {
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileEquals
     */
    function assert_file_equals(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_file_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileEqualsCanonicalizing')) {
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (canonicalizing).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileEqualsCanonicalizing
     */
    function assert_file_equals_canonicalizing(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_file_equals_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileEqualsIgnoringCase')) {
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (ignoring case).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileEqualsIgnoringCase
     */
    function assert_file_equals_ignoring_case(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_file_equals_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileNotEquals')) {
    /**
     * Asserts that the contents of one file is not equal to the contents of
     * another file.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileNotEquals
     */
    function assert_file_not_equals(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_file_not_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileNotEqualsCanonicalizing')) {
    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (canonicalizing).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileNotEqualsCanonicalizing
     */
    function assert_file_not_equals_canonicalizing(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_file_not_equals_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileNotEqualsIgnoringCase')) {
    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (ignoring case).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileNotEqualsIgnoringCase
     */
    function assert_file_not_equals_ignoring_case(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_file_not_equals_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringEqualsFile')) {
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringEqualsFile
     */
    function assert_string_equals_file(string $expected_file, string $actual_string, string $message = ''): void
    {
        Assert::assert_string_equals_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringEqualsFileCanonicalizing')) {
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (canonicalizing).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringEqualsFileCanonicalizing
     */
    function assert_string_equals_file_canonicalizing(string $expected_file, string $actual_string, string $message = ''): void
    {
        Assert::assert_string_equals_file_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringEqualsFileIgnoringCase')) {
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (ignoring case).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringEqualsFileIgnoringCase
     */
    function assert_string_equals_file_ignoring_case(string $expected_file, string $actual_string, string $message = ''): void
    {
        Assert::assert_string_equals_file_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringNotEqualsFile')) {
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringNotEqualsFile
     */
    function assert_string_not_equals_file(string $expected_file, string $actual_string, string $message = ''): void
    {
        Assert::assert_string_not_equals_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringNotEqualsFileCanonicalizing')) {
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (canonicalizing).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringNotEqualsFileCanonicalizing
     */
    function assert_string_not_equals_file_canonicalizing(string $expected_file, string $actual_string, string $message = ''): void
    {
        Assert::assert_string_not_equals_file_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringNotEqualsFileIgnoringCase')) {
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (ignoring case).
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringNotEqualsFileIgnoringCase
     */
    function assert_string_not_equals_file_ignoring_case(string $expected_file, string $actual_string, string $message = ''): void
    {
        Assert::assert_string_not_equals_file_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsReadable')) {
    /**
     * Asserts that a file/dir is readable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsReadable
     */
    function assert_is_readable(string $filename, string $message = ''): void
    {
        Assert::assert_is_readable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotReadable')) {
    /**
     * Asserts that a file/dir exists and is not readable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotReadable
     */
    function assert_is_not_readable(string $filename, string $message = ''): void
    {
        Assert::assert_is_not_readable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsWritable')) {
    /**
     * Asserts that a file/dir exists and is writable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsWritable
     */
    function assert_is_writable(string $filename, string $message = ''): void
    {
        Assert::assert_is_writable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotWritable')) {
    /**
     * Asserts that a file/dir exists and is not writable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotWritable
     */
    function assert_is_not_writable(string $filename, string $message = ''): void
    {
        Assert::assert_is_not_writable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDirectoryExists')) {
    /**
     * Asserts that a directory exists.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDirectoryExists
     */
    function assert_directory_exists(string $directory, string $message = ''): void
    {
        Assert::assert_directory_exists(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDirectoryDoesNotExist')) {
    /**
     * Asserts that a directory does not exist.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDirectoryDoesNotExist
     */
    function assert_directory_does_not_exist(string $directory, string $message = ''): void
    {
        Assert::assert_directory_does_not_exist(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDirectoryIsReadable')) {
    /**
     * Asserts that a directory exists and is readable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDirectoryIsReadable
     */
    function assert_directory_is_readable(string $directory, string $message = ''): void
    {
        Assert::assert_directory_is_readable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDirectoryIsNotReadable')) {
    /**
     * Asserts that a directory exists and is not readable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDirectoryIsNotReadable
     */
    function assert_directory_is_not_readable(string $directory, string $message = ''): void
    {
        Assert::assert_directory_is_not_readable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDirectoryIsWritable')) {
    /**
     * Asserts that a directory exists and is writable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDirectoryIsWritable
     */
    function assert_directory_is_writable(string $directory, string $message = ''): void
    {
        Assert::assert_directory_is_writable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDirectoryIsNotWritable')) {
    /**
     * Asserts that a directory exists and is not writable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDirectoryIsNotWritable
     */
    function assert_directory_is_not_writable(string $directory, string $message = ''): void
    {
        Assert::assert_directory_is_not_writable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileExists')) {
    /**
     * Asserts that a file exists.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileExists
     */
    function assert_file_exists(string $filename, string $message = ''): void
    {
        Assert::assert_file_exists(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileDoesNotExist')) {
    /**
     * Asserts that a file does not exist.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileDoesNotExist
     */
    function assert_file_does_not_exist(string $filename, string $message = ''): void
    {
        Assert::assert_file_does_not_exist(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileIsReadable')) {
    /**
     * Asserts that a file exists and is readable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileIsReadable
     */
    function assert_file_is_readable(string $file, string $message = ''): void
    {
        Assert::assert_file_is_readable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileIsNotReadable')) {
    /**
     * Asserts that a file exists and is not readable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileIsNotReadable
     */
    function assert_file_is_not_readable(string $file, string $message = ''): void
    {
        Assert::assert_file_is_not_readable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileIsWritable')) {
    /**
     * Asserts that a file exists and is writable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileIsWritable
     */
    function assert_file_is_writable(string $file, string $message = ''): void
    {
        Assert::assert_file_is_writable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileIsNotWritable')) {
    /**
     * Asserts that a file exists and is not writable.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileIsNotWritable
     */
    function assert_file_is_not_writable(string $file, string $message = ''): void
    {
        Assert::assert_file_is_not_writable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertTrue')) {
    /**
     * Asserts that a condition is true.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert true $condition
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertTrue
     */
    function assert_true(mixed $condition, string $message = ''): void
    {
        Assert::assert_true(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotTrue')) {
    /**
     * Asserts that a condition is not true.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !true $condition
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotTrue
     */
    function assert_not_true(mixed $condition, string $message = ''): void
    {
        Assert::assert_not_true(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFalse')) {
    /**
     * Asserts that a condition is false.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert false $condition
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFalse
     */
    function assert_false(mixed $condition, string $message = ''): void
    {
        Assert::assert_false(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotFalse')) {
    /**
     * Asserts that a condition is not false.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !false $condition
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotFalse
     */
    function assert_not_false(mixed $condition, string $message = ''): void
    {
        Assert::assert_not_false(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNull')) {
    /**
     * Asserts that a variable is null.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert null $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNull
     */
    function assert_null(mixed $actual, string $message = ''): void
    {
        Assert::assert_null(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotNull')) {
    /**
     * Asserts that a variable is not null.
     *
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !null $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotNull
     */
    function assert_not_null(mixed $actual, string $message = ''): void
    {
        Assert::assert_not_null(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFinite')) {
    /**
     * Asserts that a variable is finite.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFinite
     */
    function assert_finite(mixed $actual, string $message = ''): void
    {
        Assert::assert_finite(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertInfinite')) {
    /**
     * Asserts that a variable is infinite.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertInfinite
     */
    function assert_infinite(mixed $actual, string $message = ''): void
    {
        Assert::assert_infinite(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNan')) {
    /**
     * Asserts that a variable is nan.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNan
     */
    function assert_nan(mixed $actual, string $message = ''): void
    {
        Assert::assert_nan(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertObjectHasProperty')) {
    /**
     * Asserts that an object has a specified property.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertObjectHasProperty
     */
    function assert_object_has_property(string $property_name, object $object, string $message = ''): void
    {
        Assert::assert_object_has_property(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertObjectNotHasProperty')) {
    /**
     * Asserts that an object does not have a specified property.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertObjectNotHasProperty
     */
    function assert_object_not_has_property(string $property_name, object $object, string $message = ''): void
    {
        Assert::assert_object_not_has_property(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertSame')) {
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
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertSame
     */
    function assert_same(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_same(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotSame')) {
    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotSame
     */
    function assert_not_same(mixed $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_not_same(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertInstanceOf')) {
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
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertInstanceOf
     */
    function assert_instance_of(string $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_instance_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotInstanceOf')) {
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
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotInstanceOf
     */
    function assert_not_instance_of(string $expected, mixed $actual, string $message = ''): void
    {
        Assert::assert_not_instance_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsArray')) {
    /**
     * Asserts that a variable is of type array.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert array<mixed> $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsArray
     */
    function assert_is_array(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_array(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsBool')) {
    /**
     * Asserts that a variable is of type bool.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert bool $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsBool
     */
    function assert_is_bool(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_bool(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsFloat')) {
    /**
     * Asserts that a variable is of type float.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert float $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsFloat
     */
    function assert_is_float(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_float(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsInt')) {
    /**
     * Asserts that a variable is of type int.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert int $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsInt
     */
    function assert_is_int(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_int(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNumeric')) {
    /**
     * Asserts that a variable is of type numeric.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert numeric $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNumeric
     */
    function assert_is_numeric(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_numeric(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsObject')) {
    /**
     * Asserts that a variable is of type object.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert object $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsObject
     */
    function assert_is_object(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_object(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsResource')) {
    /**
     * Asserts that a variable is of type resource.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert resource $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsResource
     */
    function assert_is_resource(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsClosedResource')) {
    /**
     * Asserts that a variable is of type resource and is closed.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert resource $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsClosedResource
     */
    function assert_is_closed_resource(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_closed_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsString')) {
    /**
     * Asserts that a variable is of type string.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert string $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsString
     */
    function assert_is_string(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsScalar')) {
    /**
     * Asserts that a variable is of type scalar.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert scalar $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsScalar
     */
    function assert_is_scalar(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_scalar(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsCallable')) {
    /**
     * Asserts that a variable is of type callable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert callable $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsCallable
     */
    function assert_is_callable(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_callable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsIterable')) {
    /**
     * Asserts that a variable is of type iterable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert iterable<mixed> $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsIterable
     */
    function assert_is_iterable(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_iterable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotArray')) {
    /**
     * Asserts that a variable is not of type array.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !array<mixed> $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotArray
     */
    function assert_is_not_array(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_array(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotBool')) {
    /**
     * Asserts that a variable is not of type bool.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !bool $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotBool
     */
    function assert_is_not_bool(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_bool(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotFloat')) {
    /**
     * Asserts that a variable is not of type float.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !float $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotFloat
     */
    function assert_is_not_float(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_float(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotInt')) {
    /**
     * Asserts that a variable is not of type int.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !int $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotInt
     */
    function assert_is_not_int(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_int(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotNumeric')) {
    /**
     * Asserts that a variable is not of type numeric.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !numeric $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotNumeric
     */
    function assert_is_not_numeric(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_numeric(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotObject')) {
    /**
     * Asserts that a variable is not of type object.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !object $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotObject
     */
    function assert_is_not_object(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_object(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotResource')) {
    /**
     * Asserts that a variable is not of type resource.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !resource $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotResource
     */
    function assert_is_not_resource(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotClosedResource')) {
    /**
     * Asserts that a variable is not of type resource.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !resource $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotClosedResource
     */
    function assert_is_not_closed_resource(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_closed_resource(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotString')) {
    /**
     * Asserts that a variable is not of type string.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !string $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotString
     */
    function assert_is_not_string(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotScalar')) {
    /**
     * Asserts that a variable is not of type scalar.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !scalar $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotScalar
     */
    function assert_is_not_scalar(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_scalar(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotCallable')) {
    /**
     * Asserts that a variable is not of type callable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !callable $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotCallable
     */
    function assert_is_not_callable(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_callable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertIsNotIterable')) {
    /**
     * Asserts that a variable is not of type iterable.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @phpstan-assert !iterable<mixed> $actual
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertIsNotIterable
     */
    function assert_is_not_iterable(mixed $actual, string $message = ''): void
    {
        Assert::assert_is_not_iterable(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertMatchesRegularExpression')) {
    /**
     * Asserts that a string matches a given regular expression.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertMatchesRegularExpression
     */
    function assert_matches_regular_expression(string $pattern, string $string, string $message = ''): void
    {
        Assert::assert_matches_regular_expression(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertDoesNotMatchRegularExpression')) {
    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertDoesNotMatchRegularExpression
     */
    function assert_does_not_match_regular_expression(string $pattern, string $string, string $message = ''): void
    {
        Assert::assert_does_not_match_regular_expression(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertSameSize')) {
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
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertSameSize
     */
    function assert_same_size(Countable|iterable $expected, Countable|iterable $actual, string $message = ''): void
    {
        Assert::assert_same_size(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertNotSameSize')) {
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
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertNotSameSize
     */
    function assert_not_same_size(Countable|iterable $expected, Countable|iterable $actual, string $message = ''): void
    {
        Assert::assert_not_same_size(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringContainsStringIgnoringLineEndings')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringContainsStringIgnoringLineEndings
     */
    function assert_string_contains_string_ignoring_line_endings(string $needle, string $haystack, string $message = ''): void
    {
        Assert::assert_string_contains_string_ignoring_line_endings(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringEqualsStringIgnoringLineEndings')) {
    /**
     * Asserts that two strings are equal except for line endings.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringEqualsStringIgnoringLineEndings
     */
    function assert_string_equals_string_ignoring_line_endings(string $expected, string $actual, string $message = ''): void
    {
        Assert::assert_string_equals_string_ignoring_line_endings(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileMatchesFormat')) {
    /**
     * Asserts that a string matches a given format string.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileMatchesFormat
     */
    function assert_file_matches_format(string $format, string $actual_file, string $message = ''): void
    {
        Assert::assert_file_matches_format(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertFileMatchesFormatFile')) {
    /**
     * Asserts that a string matches a given format string.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertFileMatchesFormatFile
     */
    function assert_file_matches_format_file(string $format_file, string $actual_file, string $message = ''): void
    {
        Assert::assert_file_matches_format_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringMatchesFormat')) {
    /**
     * Asserts that a string matches a given format string.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringMatchesFormat
     */
    function assert_string_matches_format(string $format, string $string, string $message = ''): void
    {
        Assert::assert_string_matches_format(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringMatchesFormatFile')) {
    /**
     * Asserts that a string matches a given format file.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringMatchesFormatFile
     */
    function assert_string_matches_format_file(string $format_file, string $string, string $message = ''): void
    {
        Assert::assert_string_matches_format_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringStartsWith')) {
    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param non-empty-string $prefix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringStartsWith
     */
    function assert_string_starts_with(string $prefix, string $string, string $message = ''): void
    {
        Assert::assert_string_starts_with(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringStartsNotWith')) {
    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param non-empty-string $prefix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringStartsNotWith
     */
    function assert_string_starts_not_with(string $prefix, string $string, string $message = ''): void
    {
        Assert::assert_string_starts_not_with(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringContainsString')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringContainsString
     */
    function assert_string_contains_string(string $needle, string $haystack, string $message = ''): void
    {
        Assert::assert_string_contains_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringContainsStringIgnoringCase')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringContainsStringIgnoringCase
     */
    function assert_string_contains_string_ignoring_case(string $needle, string $haystack, string $message = ''): void
    {
        Assert::assert_string_contains_string_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringNotContainsString')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringNotContainsString
     */
    function assert_string_not_contains_string(string $needle, string $haystack, string $message = ''): void
    {
        Assert::assert_string_not_contains_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringNotContainsStringIgnoringCase')) {
    /**
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringNotContainsStringIgnoringCase
     */
    function assert_string_not_contains_string_ignoring_case(string $needle, string $haystack, string $message = ''): void
    {
        Assert::assert_string_not_contains_string_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringEndsWith')) {
    /**
     * Asserts that a string ends with a given suffix.
     *
     * @param non-empty-string $suffix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringEndsWith
     */
    function assert_string_ends_with(string $suffix, string $string, string $message = ''): void
    {
        Assert::assert_string_ends_with(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertStringEndsNotWith')) {
    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @param non-empty-string $suffix
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertStringEndsNotWith
     */
    function assert_string_ends_not_with(string $suffix, string $string, string $message = ''): void
    {
        Assert::assert_string_ends_not_with(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertXmlFileEqualsXmlFile')) {
    /**
     * Asserts that two XML files are equal.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws XmlException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertXmlFileEqualsXmlFile
     */
    function assert_xml_file_equals_xml_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        Assert::assert_xml_file_equals_xml_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertXmlFileNotEqualsXmlFile')) {
    /**
     * Asserts that two XML files are not equal.
     *
     * @throws \PHPUnit\Util\Exception
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertXmlFileNotEqualsXmlFile
     */
    function assert_xml_file_not_equals_xml_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        Assert::assert_xml_file_not_equals_xml_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertXmlStringEqualsXmlFile')) {
    /**
     * Asserts that two XML documents are equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertXmlStringEqualsXmlFile
     */
    function assert_xml_string_equals_xml_file(string $expected_file, string $actual_xml, string $message = ''): void
    {
        Assert::assert_xml_string_equals_xml_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertXmlStringNotEqualsXmlFile')) {
    /**
     * Asserts that two XML documents are not equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertXmlStringNotEqualsXmlFile
     */
    function assert_xml_string_not_equals_xml_file(string $expected_file, string $actual_xml, string $message = ''): void
    {
        Assert::assert_xml_string_not_equals_xml_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertXmlStringEqualsXmlString')) {
    /**
     * Asserts that two XML documents are equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertXmlStringEqualsXmlString
     */
    function assert_xml_string_equals_xml_string(string $expected_xml, string $actual_xml, string $message = ''): void
    {
        Assert::assert_xml_string_equals_xml_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertXmlStringNotEqualsXmlString')) {
    /**
     * Asserts that two XML documents are not equal.
     *
     * @throws ExpectationFailedException
     * @throws XmlException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertXmlStringNotEqualsXmlString
     */
    function assert_xml_string_not_equals_xml_string(string $expected_xml, string $actual_xml, string $message = ''): void
    {
        Assert::assert_xml_string_not_equals_xml_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertThat')) {
    /**
     * Evaluates a PHPUnit\Framework\Constraint matcher object.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertThat
     */
    function assert_that(mixed $value, Constraint $constraint, string $message = ''): void
    {
        Assert::assert_that(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJson')) {
    /**
     * Asserts that a string is a valid JSON string.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJson
     */
    function assert_json(string $actual, string $message = ''): void
    {
        Assert::assert_json(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJsonStringEqualsJsonString')) {
    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJsonStringEqualsJsonString
     */
    function assert_json_string_equals_json_string(string $expected_json, string $actual_json, string $message = ''): void
    {
        Assert::assert_json_string_equals_json_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJsonStringNotEqualsJsonString')) {
    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJsonStringNotEqualsJsonString
     */
    function assert_json_string_not_equals_json_string(string $expected_json, string $actual_json, string $message = ''): void
    {
        Assert::assert_json_string_not_equals_json_string(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJsonStringEqualsJsonFile')) {
    /**
     * Asserts that the generated JSON encoded object and the content of the given file are equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJsonStringEqualsJsonFile
     */
    function assert_json_string_equals_json_file(string $expected_file, string $actual_json, string $message = ''): void
    {
        Assert::assert_json_string_equals_json_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJsonStringNotEqualsJsonFile')) {
    /**
     * Asserts that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJsonStringNotEqualsJsonFile
     */
    function assert_json_string_not_equals_json_file(string $expected_file, string $actual_json, string $message = ''): void
    {
        Assert::assert_json_string_not_equals_json_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJsonFileEqualsJsonFile')) {
    /**
     * Asserts that two JSON files are equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJsonFileEqualsJsonFile
     */
    function assert_json_file_equals_json_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        Assert::assert_json_file_equals_json_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\assertJsonFileNotEqualsJsonFile')) {
    /**
     * Asserts that two JSON files are not equal.
     *
     * @throws ExpectationFailedException
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @see Assert::assertJsonFileNotEqualsJsonFile
     */
    function assert_json_file_not_equals_json_file(string $expected_file, string $actual_file, string $message = ''): void
    {
        Assert::assert_json_file_not_equals_json_file(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\logicalAnd')) {
    function logical_and(mixed ...$constraints): Logical_And
    {
        return Assert::logical_and(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\logicalOr')) {
    function logical_or(mixed ...$constraints): Logical_Or
    {
        return Assert::logical_or(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\logicalNot')) {
    function logical_not(Constraint $constraint): Logical_Not
    {
        return Assert::logical_not(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\logicalXor')) {
    function logical_xor(mixed ...$constraints): Logical_Xor
    {
        return Assert::logical_xor(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\anything')) {
    function anything(): Is_Anything
    {
        return Assert::anything();
    }
}
if (!function_exists('PHPUnit\Framework\isTrue')) {
    function is_true(): Is_True
    {
        return Assert::is_true();
    }
}
if (!function_exists('PHPUnit\Framework\isFalse')) {
    function is_false(): Is_False
    {
        return Assert::is_false();
    }
}
if (!function_exists('PHPUnit\Framework\isJson')) {
    function is_json(): Is_Json
    {
        return Assert::is_json();
    }
}
if (!function_exists('PHPUnit\Framework\isNull')) {
    function is_null(): Is_Null
    {
        return Assert::is_null();
    }
}
if (!function_exists('PHPUnit\Framework\isFinite')) {
    function is_finite(): Is_Finite
    {
        return Assert::is_finite();
    }
}
if (!function_exists('PHPUnit\Framework\isInfinite')) {
    function is_infinite(): Is_Infinite
    {
        return Assert::is_infinite();
    }
}
if (!function_exists('PHPUnit\Framework\isNan')) {
    function is_nan(): Is_Nan
    {
        return Assert::is_nan();
    }
}
if (!function_exists('PHPUnit\Framework\containsEqual')) {
    function contains_equal(mixed $value): Traversable_Contains_Equal
    {
        return Assert::contains_equal(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\containsIdentical')) {
    function contains_identical(mixed $value): Traversable_Contains_Identical
    {
        return Assert::contains_identical(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyArray')) {
    function contains_only_array(): Traversable_Contains_Only
    {
        return Assert::contains_only_array();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyBool')) {
    function contains_only_bool(): Traversable_Contains_Only
    {
        return Assert::contains_only_bool();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyCallable')) {
    function contains_only_callable(): Traversable_Contains_Only
    {
        return Assert::contains_only_callable();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyFloat')) {
    function contains_only_float(): Traversable_Contains_Only
    {
        return Assert::contains_only_float();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyInt')) {
    function contains_only_int(): Traversable_Contains_Only
    {
        return Assert::contains_only_int();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyIterable')) {
    function contains_only_iterable(): Traversable_Contains_Only
    {
        return Assert::contains_only_iterable();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyNull')) {
    function contains_only_null(): Traversable_Contains_Only
    {
        return Assert::contains_only_null();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyNumeric')) {
    function contains_only_numeric(): Traversable_Contains_Only
    {
        return Assert::contains_only_numeric();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyObject')) {
    function contains_only_object(): Traversable_Contains_Only
    {
        return Assert::contains_only_object();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyResource')) {
    function contains_only_resource(): Traversable_Contains_Only
    {
        return Assert::contains_only_resource();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyClosedResource')) {
    function contains_only_closed_resource(): Traversable_Contains_Only
    {
        return Assert::contains_only_closed_resource();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyScalar')) {
    function contains_only_scalar(): Traversable_Contains_Only
    {
        return Assert::contains_only_scalar();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyString')) {
    function contains_only_string(): Traversable_Contains_Only
    {
        return Assert::contains_only_string();
    }
}
if (!function_exists('PHPUnit\Framework\containsOnlyInstancesOf')) {
    function contains_only_instances_of(string $class_name): Traversable_Contains_Only
    {
        return Assert::contains_only_instances_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\arrayHasKey')) {
    function array_has_key(mixed $key): Array_Has_Key
    {
        return Assert::array_has_key(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\isList')) {
    function is_list(): Is_List
    {
        return Assert::is_list();
    }
}
if (!function_exists('PHPUnit\Framework\equalTo')) {
    function equal_to(mixed $value): Is_Equal
    {
        return Assert::equal_to(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\equalToCanonicalizing')) {
    function equal_to_canonicalizing(mixed $value): Is_Equal_Canonicalizing
    {
        return Assert::equal_to_canonicalizing(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\equalToIgnoringCase')) {
    function equal_to_ignoring_case(mixed $value): Is_Equal_Ignoring_Case
    {
        return Assert::equal_to_ignoring_case(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\equalToWithDelta')) {
    function equal_to_with_delta(mixed $value, float $delta): Is_Equal_With_Delta
    {
        return Assert::equal_to_with_delta(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\isEmpty')) {
    function is_empty(): Is_Empty
    {
        return Assert::is_empty();
    }
}
if (!function_exists('PHPUnit\Framework\isWritable')) {
    function is_writable(): Is_Writable
    {
        return Assert::is_writable();
    }
}
if (!function_exists('PHPUnit\Framework\isReadable')) {
    function is_readable(): Is_Readable
    {
        return Assert::is_readable();
    }
}
if (!function_exists('PHPUnit\Framework\directoryExists')) {
    function directory_exists(): Directory_Exists
    {
        return Assert::directory_exists();
    }
}
if (!function_exists('PHPUnit\Framework\fileExists')) {
    function file_exists(): File_Exists
    {
        return Assert::file_exists();
    }
}
if (!function_exists('PHPUnit\Framework\greaterThan')) {
    function greater_than(mixed $value): Greater_Than
    {
        return Assert::greater_than(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\greaterThanOrEqual')) {
    function greater_than_or_equal(mixed $value): Logical_Or
    {
        return Assert::greater_than_or_equal(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\identicalTo')) {
    function identical_to(mixed $value): Is_Identical
    {
        return Assert::identical_to(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\isInstanceOf')) {
    function is_instance_of(string $class_name): Is_Instance_Of
    {
        return Assert::is_instance_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\isArray')) {
    function is_array(): Is_Type
    {
        return Assert::is_array();
    }
}
if (!function_exists('PHPUnit\Framework\isBool')) {
    function is_bool(): Is_Type
    {
        return Assert::is_bool();
    }
}
if (!function_exists('PHPUnit\Framework\isCallable')) {
    function is_callable(): Is_Type
    {
        return Assert::is_callable();
    }
}
if (!function_exists('PHPUnit\Framework\isFloat')) {
    function is_float(): Is_Type
    {
        return Assert::is_float();
    }
}
if (!function_exists('PHPUnit\Framework\isInt')) {
    function is_int(): Is_Type
    {
        return Assert::is_int();
    }
}
if (!function_exists('PHPUnit\Framework\isIterable')) {
    function is_iterable(): Is_Type
    {
        return Assert::is_iterable();
    }
}
if (!function_exists('PHPUnit\Framework\isNumeric')) {
    function is_numeric(): Is_Type
    {
        return Assert::is_numeric();
    }
}
if (!function_exists('PHPUnit\Framework\isObject')) {
    function is_object(): Is_Type
    {
        return Assert::is_object();
    }
}
if (!function_exists('PHPUnit\Framework\isResource')) {
    function is_resource(): Is_Type
    {
        return Assert::is_resource();
    }
}
if (!function_exists('PHPUnit\Framework\isClosedResource')) {
    function is_closed_resource(): Is_Type
    {
        return Assert::is_closed_resource();
    }
}
if (!function_exists('PHPUnit\Framework\isScalar')) {
    function is_scalar(): Is_Type
    {
        return Assert::is_scalar();
    }
}
if (!function_exists('PHPUnit\Framework\isString')) {
    function is_string(): Is_Type
    {
        return Assert::is_string();
    }
}
if (!function_exists('PHPUnit\Framework\lessThan')) {
    function less_than(mixed $value): Less_Than
    {
        return Assert::less_than(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\lessThanOrEqual')) {
    function less_than_or_equal(mixed $value): Logical_Or
    {
        return Assert::less_than_or_equal(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\matchesRegularExpression')) {
    function matches_regular_expression(string $pattern): Regular_Expression
    {
        return Assert::matches_regular_expression(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\matches')) {
    function matches(string $string): String_Matches_Format_Description
    {
        return Assert::matches(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\stringStartsWith')) {
    function string_starts_with(string $prefix): String_Starts_With
    {
        return Assert::string_starts_with(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\stringContains')) {
    function string_contains(string $string, bool $case = true): String_Contains
    {
        return Assert::string_contains(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\stringEndsWith')) {
    function string_ends_with(string $suffix): String_Ends_With
    {
        return Assert::string_ends_with(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\stringEqualsStringIgnoringLineEndings')) {
    function string_equals_string_ignoring_line_endings(string $string): String_Equals_String_Ignoring_Line_Endings
    {
        return Assert::string_equals_string_ignoring_line_endings(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\countOf')) {
    function count_of(int $count): Count
    {
        return Assert::count_of(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\objectEquals')) {
    function object_equals(object $object, string $method = 'equals'): Object_Equals
    {
        return Assert::object_equals(...func_get_args());
    }
}
if (!function_exists('PHPUnit\Framework\callback')) {
    /**
     * @template CallbackInput of mixed
     *
     * @param callable(CallbackInput $callback): bool $callback
     *
     * @return Callback<CallbackInput>
     */
    function callback(callable $callback): Callback
    {
        return Assert::callback($callback);
    }
}
if (!function_exists('PHPUnit\Framework\any')) {
    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6461
     */
    function any(): Any_Invoked_Count_Matcher
    {
        return new Any_Invoked_Count_Matcher();
    }
}
if (!function_exists('PHPUnit\Framework\never')) {
    /**
     * Returns a matcher that matches when the method is never executed.
     */
    function never(): Invoked_Count_Matcher
    {
        return new Invoked_Count_Matcher(0);
    }
}
if (!function_exists('PHPUnit\Framework\atLeast')) {
    /**
     * Returns a matcher that matches when the method is executed
     * at least N times.
     */
    function at_least(int $required_invocations): Invoked_At_Least_Count_Matcher
    {
        if ($required_invocations < 1) {
            Event_Facade::emitter()->test_triggered_phpunit_deprecation(null, 'Calling atLeast() with an argument that is not positive is deprecated.' . PHP_EOL . 'This will become an error in PHPUnit 14.');
        }
        return new Invoked_At_Least_Count_Matcher($required_invocations);
    }
}
if (!function_exists('PHPUnit\Framework\atLeastOnce')) {
    /**
     * Returns a matcher that matches when the method is executed at least once.
     */
    function at_least_once(): Invoked_At_Least_Once_Matcher
    {
        return new Invoked_At_Least_Once_Matcher();
    }
}
if (!function_exists('PHPUnit\Framework\once')) {
    /**
     * Returns a matcher that matches when the method is executed exactly once.
     */
    function once(): Invoked_Count_Matcher
    {
        return new Invoked_Count_Matcher(1);
    }
}
if (!function_exists('PHPUnit\Framework\exactly')) {
    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     */
    function exactly(int $count): Invoked_Count_Matcher
    {
        return new Invoked_Count_Matcher($count);
    }
}
if (!function_exists('PHPUnit\Framework\atMost')) {
    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     */
    function at_most(int $allowed_invocations): Invoked_At_Most_Count_Matcher
    {
        return new Invoked_At_Most_Count_Matcher($allowed_invocations);
    }
}
if (!function_exists('PHPUnit\Framework\throwException')) {
    function throw_exception(Throwable $exception): Exception_Stub
    {
        return new Exception_Stub($exception);
    }
}