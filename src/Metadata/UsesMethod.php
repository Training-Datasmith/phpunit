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
namespace Php_Unit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Uses_Method extends Metadata
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    protected function __construct(Level $level, private string $class_name, private string $method_name)
    {
        parent::__construct($level);
    }
    public function is_uses_method(): true
    {
        return true;
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
}