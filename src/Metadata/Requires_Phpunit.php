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

use Php_Unit\Metadata\Version\Requirement;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Requires_Phpunit extends Metadata
{
    protected function __construct(Level $level, private Requirement $version_requirement)
    {
        parent::__construct($level);
    }
    public function is_requires_phpunit(): true
    {
        return true;
    }
    public function version_requirement(): Requirement
    {
        return $this->version_requirement;
    }
}