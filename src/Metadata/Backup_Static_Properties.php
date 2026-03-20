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
final readonly class Backup_Static_Properties extends Metadata
{
    protected function __construct(Level $level, private bool $enabled)
    {
        parent::__construct($level);
    }
    public function is_backup_static_properties(): true
    {
        return true;
    }
    public function enabled(): bool
    {
        return $this->enabled;
    }
}