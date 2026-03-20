<?php

declare(strict_types=1);

/**
 * Example 1: Basic test case demonstrating core PHPUnit assertions.
 *
 * Run with:
 *   vendor/bin/phpunit examples/01_basic_test_case.php
 *
 * Or register the examples/ directory in phpunit.xml and run the full suite.
 */

use PHPUnit\Framework\TestCase;

/**
 * Demonstrates the most common assertion methods available in TestCase.
 *
 * Each test method is independent. PHPUnit instantiates the class freshly
 * for every test method, so properties set in one test are not visible
 * in another.
 */
final class BasicAssertionsTest extends TestCase
{
    // -----------------------------------------------------------------------
    // 1. Equality vs. identity
    // -----------------------------------------------------------------------

    public function test_assert_equals_compares_values(): void
    {
        // assertEquals uses loose comparison (same value, possibly different type)
        $this->assertEquals(42, 42);
        $this->assertEquals('hello', 'hello');
    }

    public function test_assert_same_compares_value_and_type(): void
    {
        // assertSame uses strict === comparison
        $this->assertSame(42, 42);
        $this->assertSame('hello', 'hello');
    }

    public function test_assert_not_equals_and_not_same(): void
    {
        $this->assertNotEquals(1, 2);
        $this->assertNotSame('1', 1); // different types
    }

    // -----------------------------------------------------------------------
    // 2. Boolean and null checks
    // -----------------------------------------------------------------------

    public function test_boolean_assertions(): void
    {
        $this->assertTrue(1 + 1 === 2);
        $this->assertFalse(1 === 2);
    }

    public function test_null_assertions(): void
    {
        $value = null;
        $this->assertNull($value);

        $value = 0;
        $this->assertNotNull($value);
    }

    // -----------------------------------------------------------------------
    // 3. Array and collection assertions
    // -----------------------------------------------------------------------

    public function test_array_assertions(): void
    {
        $items = ['apple', 'banana', 'cherry'];

        $this->assertCount(3, $items);
        $this->assertContains('banana', $items);
        $this->assertNotContains('durian', $items);
        $this->assertArrayHasKey(0, $items);
    }

    public function test_array_equality_ignoring_keys(): void
    {
        $expected = ['a' => 1, 'b' => 2];
        $actual   = ['b' => 2, 'a' => 1];

        // assertEqualsCanonicalizing sorts both arrays before comparing,
        // which is useful when key order is not significant.
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    // -----------------------------------------------------------------------
    // 4. String assertions
    // -----------------------------------------------------------------------

    public function test_string_assertions(): void
    {
        $greeting = 'Hello, World!';

        $this->assertStringContainsString('World', $greeting);
        $this->assertStringStartsWith('Hello', $greeting);
        $this->assertStringEndsWith('!', $greeting);
        $this->assertMatchesRegularExpression('/^Hello,\s\w+!$/', $greeting);
    }

    // -----------------------------------------------------------------------
    // 5. Exception testing
    // -----------------------------------------------------------------------

    public function test_exception_is_thrown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be positive');

        // This code must throw the expected exception for the test to pass.
        $this->triggerInvalidArgument(-1);
    }

    private function triggerInvalidArgument(int $value): void
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Value must be positive, got ' . $value);
        }
    }

    // -----------------------------------------------------------------------
    // 6. setUp / tearDown lifecycle
    // -----------------------------------------------------------------------

    /** @var list<string> */
    private array $log = [];

    protected function setUp(): void
    {
        // Called before each test method. Initialise shared state here.
        $this->log = [];
    }

    protected function tearDown(): void
    {
        // Called after each test method. Release resources here.
        $this->log = [];
    }

    public function test_setup_initialises_empty_log(): void
    {
        $this->assertEmpty($this->log);
    }
}
