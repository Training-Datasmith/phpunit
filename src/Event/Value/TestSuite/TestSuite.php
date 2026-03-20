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
namespace Php_Unit\Event\Test_Suite;

use Php_Unit\Event\Code\Test_Collection;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Test_Suite
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(private string $name, private int $count, private Test_Collection $tests)
    {
    }
    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
    public function count(): int
    {
        return $this->count;
    }
    public function tests(): Test_Collection
    {
        return $this->tests;
    }
    /**
     * @phpstan-assert-if-true TestSuiteWithName $this
     */
    public function is_with_name(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true TestSuiteForTestClass $this
     */
    public function is_for_test_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true TestSuiteForTestMethodWithDataProvider $this
     */
    public function is_for_test_method_with_data_provider(): bool
    {
        return false;
    }
}