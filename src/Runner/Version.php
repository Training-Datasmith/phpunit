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
namespace Php_Unit\Runner;

use function array_slice;
use function assert;
use function dirname;
use function explode;
use function implode;
use Sebastian_Bergmann\Version as VersionId;
use function str_contains;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Version
{
    private static string $phar_version = '';
    private static string $version = '';
    /**
     * @return non-empty-string
     */
    public static function id(): string
    {
        if (self::$phar_version !== '') {
            return self::$phar_version;
        }
        if (self::$version === '') {
            self::$version = (new Version_Id('13.1', dirname(__DIR__, 2)))->as_string();
        }
        return self::$version;
    }
    /**
     * @return non-empty-string
     */
    public static function series(): string
    {
        if (str_contains(self::id(), '-')) {
            $version = explode('-', self::id(), 2)[0];
        } else {
            $version = self::id();
        }
        return implode('.', array_slice(explode('.', $version), 0, 2));
    }
    /**
     * @return positive-int
     */
    public static function major_version_number(): int
    {
        $major_version = (int) explode('.', self::series())[0];
        assert($major_version > 0);
        return $major_version;
    }
    /**
     * @return non-empty-string
     */
    public static function get_version_string(): string
    {
        return 'PHPUnit ' . self::id() . ' by Sebastian Bergmann and contributors.';
    }
}