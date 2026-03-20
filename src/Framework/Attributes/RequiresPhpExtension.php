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
final readonly class Requires_Php_Extension
{
    /**
     * @param non-empty-string      $extension
     * @param null|non-empty-string $versionRequirement
     */
    public function __construct(private string $extension, private ?string $version_requirement = null)
    {
    }
    /**
     * @return non-empty-string
     */
    public function extension(): string
    {
        return $this->extension;
    }
    /**
     * @return null|non-empty-string
     */
    public function version_requirement(): ?string
    {
        return $this->version_requirement;
    }
}