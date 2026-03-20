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
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Ignore_Deprecations
{
    /**
     * @param null|non-empty-string $messagePattern
     */
    public function __construct(private ?string $message_pattern = null)
    {
    }
    /**
     * @return null|non-empty-string
     */
    public function message_pattern(): ?string
    {
        return $this->message_pattern;
    }
}