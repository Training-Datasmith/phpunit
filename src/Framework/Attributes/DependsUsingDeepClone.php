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
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Depends_Using_Deep_Clone
{
    /**
     * @param non-empty-string $methodName
     */
    public function __construct(private string $method_name)
    {
    }
    /**
     * @return non-empty-string
     */
    public function method_name(): string
    {
        return $this->method_name;
    }
}