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
final readonly class Test_Suite_For_Test_Method_With_Data_Provider extends Test_Suite
{
    /**
     * @param non-empty-string $name
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function __construct(string $name, int $size, Test_Collection $tests, private string $class_name, private string $method_name, private string $file, private int $line)
    {
        parent::__construct($name, $size, $tests);
    }
    /**
     * @return class-string
     */
    public function class_name(): string
    {
        return $this->class_name;
    }
    /**
     * @return non-empty-string
     */
    public function method_name(): string
    {
        return $this->method_name;
    }
    public function file(): string
    {
        return $this->file;
    }
    public function line(): int
    {
        return $this->line;
    }
    public function is_for_test_method_with_data_provider(): true
    {
        return true;
    }
}