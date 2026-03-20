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
final readonly class Test_Suite_For_Test_Class extends Test_Suite
{
    /**
     * @var class-string
     */
    private string $class_name;
    /**
     * @param class-string $name
     */
    public function __construct(string $name, int $size, Test_Collection $tests, private string $file, private int $line)
    {
        parent::__construct($name, $size, $tests);
        $this->class_name = $name;
    }
    /**
     * @return class-string
     */
    public function class_name(): string
    {
        return $this->class_name;
    }
    public function file(): string
    {
        return $this->file;
    }
    public function line(): int
    {
        return $this->line;
    }
    public function is_for_test_class(): true
    {
        return true;
    }
}