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
final readonly class Variable
{
    public function __construct(private string $name, private mixed $value, private bool $force)
    {
    }
    public function name(): string
    {
        return $this->name;
    }
    public function value(): mixed
    {
        return $this->value;
    }
    public function force(): bool
    {
        return $this->force;
    }
}