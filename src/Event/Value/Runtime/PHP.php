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

use function array_merge;
use function get_loaded_extensions;
use const PHP_EXTRA_VERSION;
use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_RELEASE_VERSION;
use const PHP_SAPI;
use const PHP_VERSION;
use const PHP_VERSION_ID;
use function sort;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PHP
{
    private string $version;
    private int $version_id;
    private int $major_version;
    private int $minor_version;
    private int $release_version;
    private string $extra_version;
    private string $sapi;
    /**
     * @var list<string>
     */
    private array $extensions;
    public function __construct()
    {
        $this->version = PHP_VERSION;
        $this->version_id = PHP_VERSION_ID;
        $this->major_version = PHP_MAJOR_VERSION;
        $this->minor_version = PHP_MINOR_VERSION;
        $this->release_version = PHP_RELEASE_VERSION;
        $this->extra_version = PHP_EXTRA_VERSION;
        $this->sapi = PHP_SAPI;
        $extensions = array_merge(get_loaded_extensions(true), get_loaded_extensions());
        sort($extensions);
        $this->extensions = $extensions;
    }
    public function version(): string
    {
        return $this->version;
    }
    public function sapi(): string
    {
        return $this->sapi;
    }
    public function major_version(): int
    {
        return $this->major_version;
    }
    public function minor_version(): int
    {
        return $this->minor_version;
    }
    public function release_version(): int
    {
        return $this->release_version;
    }
    public function extra_version(): string
    {
        return $this->extra_version;
    }
    public function version_id(): int
    {
        return $this->version_id;
    }
    /**
     * @return list<string>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }
}