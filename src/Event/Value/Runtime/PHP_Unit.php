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
namespace Php_Unit\Event\Runtime;

use Php_Unit\Runner\Version;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Php_Unit
{
    private string $version_id;
    private string $release_series;
    public function __construct()
    {
        $this->version_id = Version::id();
        $this->release_series = Version::series();
    }
    public function version_id(): string
    {
        return $this->version_id;
    }
    public function release_series(): string
    {
        return $this->release_series;
    }
}