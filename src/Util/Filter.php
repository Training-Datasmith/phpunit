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

use function array_any;
use function array_unshift;
use function defined;
use function in_array;
use function is_array;
use function is_file;
use Php_Unit\Framework\Exception;
use Php_Unit\Framework\Phpt_Assertion_Failed_Error;
use function realpath;
use function sprintf;
use function str_starts_with;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Filter
{
    /**
     * @throws Exception
     */
    public static function stack_trace_from_throwable_as_string(Throwable $t, bool $unwrap = true): string
    {
        if ($t instanceof Phpt_Assertion_Failed_Error) {
            $stack_trace = $t->synthetic_trace();
            $file = $t->synthetic_file();
            $line = $t->synthetic_line();
        } elseif ($t instanceof Exception) {
            $stack_trace = $t->get_serializable_trace();
            $file = $t->get_file();
            $line = $t->get_line();
        } else {
            if ($unwrap && $t->get_previous() !== null) {
                $t = $t->get_previous();
            }
            $stack_trace = $t->get_trace();
            $file = $t->get_file();
            $line = $t->get_line();
        }
        if (!self::frame_exists($stack_trace, $file, $line)) {
            array_unshift($stack_trace, ['file' => $file, 'line' => $line]);
        }
        return self::stack_trace_as_string($stack_trace);
    }
    /**
     * @param list<array{file: string, line: ?int, class?: class-string, function?: string, type: string}> $frames
     */
    private static function stack_trace_as_string(array $frames): string
    {
        $buffer = '';
        $prefix = defined('__PHPUNIT_PHAR_ROOT__') ? __PHPUNIT_PHAR_ROOT__ : false;
        $exclude_list = new Exclude_List();
        foreach ($frames as $frame) {
            if (self::should_print_frame($frame, $prefix, $exclude_list)) {
                $buffer .= sprintf("%s:%s\n", $frame['file'], $frame['line'] ?? '?');
            }
        }
        return $buffer;
    }
    /**
     * @param array{file?: non-empty-string} $frame
     */
    private static function should_print_frame(array $frame, false|string $prefix, Exclude_List $exclude_list): bool
    {
        if (!isset($frame['file'])) {
            return false;
        }
        $file = $frame['file'];
        $file_is_not_prefixed = $prefix === false || !str_starts_with($file, $prefix);
        // @see https://github.com/sebastianbergmann/phpunit/issues/4033
        if (isset($GLOBALS['_SERVER']['SCRIPT_NAME'])) {
            $script = realpath($GLOBALS['_SERVER']['SCRIPT_NAME']);
        } else {
            // @codeCoverageIgnoreStart
            $script = '';
            // @codeCoverageIgnoreEnd
        }
        return $file_is_not_prefixed && $file !== $script && self::file_is_excluded($file, $exclude_list) && is_file($file);
    }
    private static function file_is_excluded(string $file, Exclude_List $exclude_list): bool
    {
        return (!isset($GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST']) || !is_array($GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST']) || $GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'] === [] || !in_array($file, $GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'], true)) && !$exclude_list->is_excluded($file);
    }
    /**
     * @param list<array{file?: non-empty-string, line?: int}> $trace
     */
    private static function frame_exists(array $trace, string $file, int $line): bool
    {
        return array_any($trace, static fn(array $frame): bool => isset($frame['file'], $frame['line']) && $frame['file'] === $file && $frame['line'] === $line);
    }
}