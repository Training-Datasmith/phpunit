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
final readonly class Depends_On_Class extends Metadata
{
    /**
     * @param class-string $className
     */
    protected function __construct(Level $level, private string $class_name, private bool $deep_clone, private bool $shallow_clone)
    {
        parent::__construct($level);
    }
    public function is_depends_on_class(): true
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
    public function deep_clone(): bool
    {
        return $this->deep_clone;
    }
    public function shallow_clone(): bool
    {
        return $this->shallow_clone;
    }
}