<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Runner\Phpt;

use function preg_replace;

/**
 * Normalizes PHPT subprocess output for cross-platform comparison.
 *
 * Only line endings are normalized here. Path separators are intentionally
 * left as the platform emits them so EXPECTF placeholders such as %e
 * (DIRECTORY_SEPARATOR) continue to match on Windows.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class OutputNormalizer
{
    public static function normalize(string $output): string
    {
        return self::normalizeLineEndings($output);
    }

    private static function normalizeLineEndings(string $output): string
    {
        return preg_replace('/\r\n|\r/', "\n", $output) ?? $output;
    }
}
