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

use function getmypid;
use const PHP_EOL;
use function register_shutdown_function;
use function rtrim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Shutdown_Handler
{
    private static bool $registered = false;
    private static string $message = '';
    public static function set_message(string $message): void
    {
        self::register();
        self::$message = $message;
    }
    public static function reset_message(): void
    {
        self::$message = '';
    }
    private static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;
        $pid = getmypid();
        register_shutdown_function(static function () use ($pid): void {
            $message = rtrim(self::$message);
            if ($message === '' || $pid !== getmypid()) {
                return;
            }
            print $message . PHP_EOL;
        });
    }
}