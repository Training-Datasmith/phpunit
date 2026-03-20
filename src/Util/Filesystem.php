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
namespace Php_Unit\Util;

use function basename;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function is_dir;
use function mkdir;
use function realpath;
use function str_starts_with;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Filesystem
{
    public static function create_directory(string $directory): bool
    {
        return !(!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory));
    }
    /**
     * @param non-empty-string $path
     *
     * @return false|non-empty-string
     */
    public static function resolve_stream_or_file(string $path): false|string
    {
        if (str_starts_with($path, 'php://') || str_starts_with($path, 'socket://')) {
            return $path;
        }
        $directory = dirname($path);
        if (is_dir($directory)) {
            return realpath($directory) . DIRECTORY_SEPARATOR . basename($path);
        }
        return false;
    }
}