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
use Php_Unit\Runner\Extension\Extension;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Requires_Phpunit_Extension
{
    /**
     * @param class-string<Extension> $extensionClass
     */
    public function __construct(private string $extension_class)
    {
    }
    /**
     * @return class-string<Extension>
     */
    public function extension_class(): string
    {
        return $this->extension_class;
    }
}