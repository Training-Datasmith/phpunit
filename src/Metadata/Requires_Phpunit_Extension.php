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

use Php_Unit\Runner\Extension\Extension;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Requires_Phpunit_Extension extends Metadata
{
    /**
     * @param class-string<Extension> $extensionClass
     */
    protected function __construct(Level $level, private string $extension_class)
    {
        parent::__construct($level);
    }
    public function is_requires_phpunit_extension(): true
    {
        return true;
    }
    /**
     * @return class-string<Extension>
     */
    public function extension_class(): string
    {
        return $this->extension_class;
    }
}