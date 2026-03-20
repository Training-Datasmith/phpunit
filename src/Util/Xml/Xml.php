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

use function assert;
use const ENT_QUOTES;
use function htmlspecialchars;
use function mb_convert_encoding;
use function ord;
use function preg_replace;
use function strlen;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Xml
{
    /**
     * Escapes a string for the use in XML documents.
     *
     * Any Unicode character is allowed, excluding the surrogate blocks, FFFE,
     * and FFFF (not even as character reference).
     *
     * @see https://www.w3.org/TR/xml/#charsets
     */
    public static function prepare_string(string $string): string
    {
        $result = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '', htmlspecialchars(self::convert_to_utf8($string), ENT_QUOTES));
        assert($result !== null);
        return $result;
    }
    private static function convert_to_utf8(string $string): string
    {
        if (!self::is_utf8($string)) {
            return mb_convert_encoding($string, 'UTF-8');
        }
        return $string;
    }
    private static function is_utf8(string $string): bool
    {
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) < 0x80) {
                $n = 0;
            } elseif ((ord($string[$i]) & 0xe0) === 0xc0) {
                $n = 1;
            } elseif ((ord($string[$i]) & 0xf0) === 0xe0) {
                $n = 2;
            } elseif ((ord($string[$i]) & 0xf0) === 0xf0) {
                $n = 3;
            } else {
                return false;
            }
            for ($j = 0; $j < $n; $j++) {
                if (++$i === $length || (ord($string[$i]) & 0xc0) !== 0x80) {
                    return false;
                }
            }
        }
        return true;
    }
}