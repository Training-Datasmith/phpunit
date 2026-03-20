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
namespace Php_Unit\Framework\Attributes;

use Attribute;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class With_Environment_Variable
{
    /**
     * @param non-empty-string $environmentVariableName
     */
    public function __construct(private string $environment_variable_name, private null|string $value = null)
    {
    }
    /**
     * @return non-empty-string
     */
    public function environment_variable_name(): string
    {
        return $this->environment_variable_name;
    }
    public function value(): null|string
    {
        return $this->value;
    }
}