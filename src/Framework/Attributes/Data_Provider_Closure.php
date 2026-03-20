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
use Closure;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Data_Provider_Closure
{
    public function __construct(private Closure $closure, private bool $validate_argument_count = true)
    {
    }
    public function closure(): Closure
    {
        return $this->closure;
    }
    public function validate_argument_count(): bool
    {
        return $this->validate_argument_count;
    }
}