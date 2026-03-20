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
namespace Php_Unit\Text_Ui\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Extension_Bootstrap
{
    /**
     * @param non-empty-string     $className
     * @param array<string,string> $parameters
     */
    public function __construct(private string $class_name, private array $parameters)
    {
    }
    /**
     * @return non-empty-string
     */
    public function class_name(): string
    {
        return $this->class_name;
    }
    /**
     * @return array<string,string>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
}