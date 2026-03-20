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
namespace Php_Unit\Framework\Constraint;

use function is_string;
use function mb_detect_encoding;
use function mb_stripos;
use function mb_strtolower;
use Php_Unit\Util\Exporter;
use function sprintf;
use function str_contains;
use function strlen;
use function strtr;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class String_Contains extends Constraint
{
    private readonly string $needle;
    private readonly bool $ignore_line_endings;
    public function __construct(string $needle, private readonly bool $ignore_case = false, bool $ignore_line_endings = false)
    {
        if ($ignore_line_endings) {
            $needle = $this->normalize_line_endings($needle);
        }
        $this->needle = $needle;
        $this->ignore_line_endings = $ignore_line_endings;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        $needle = $this->needle;
        if ($this->ignore_case) {
            $needle = mb_strtolower($this->needle, 'UTF-8');
        }
        return sprintf('contains "%s" [%s](length: %s)', $needle, $this->detected_encoding($needle), strlen($needle));
    }
    public function failure_description(mixed $other): string
    {
        $stringified_haystack = Exporter::export($other);
        $haystack_encoding = $this->detected_encoding($other);
        $haystack_length = $this->haystack_length($other);
        $haystack_information = sprintf('%s [%s](length: %s) ', $stringified_haystack, $haystack_encoding, $haystack_length);
        $needle_information = $this->to_string();
        return $haystack_information . $needle_information;
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        $haystack = $other;
        if ('' === $this->needle) {
            return true;
        }
        if (!is_string($haystack)) {
            return false;
        }
        if ($this->ignore_line_endings) {
            $haystack = $this->normalize_line_endings($haystack);
        }
        if ($this->ignore_case) {
            /*
             * We must use the multibyte-safe version, so we can accurately compare non-latin uppercase characters with
             * their lowercase equivalents.
             */
            return mb_stripos($haystack, $this->needle, 0, 'UTF-8') !== false;
        }
        /*
         * Use the non-multibyte safe functions to see if the string is contained in $other.
         *
         * This function is very fast, and we don't care about the character position in the string.
         *
         * Additionally, we want this method to be binary safe, so we can check if some binary data is in other binary
         * data.
         */
        return str_contains($haystack, $this->needle);
    }
    private function detected_encoding(mixed $other): string
    {
        if ($this->ignore_case) {
            return 'Encoding ignored';
        }
        if (!is_string($other)) {
            return 'Encoding detection failed';
        }
        $detected_encoding = mb_detect_encoding($other, null, true);
        if ($detected_encoding === false) {
            return 'Encoding detection failed';
        }
        return $detected_encoding;
    }
    private function haystack_length(mixed $haystack): int
    {
        if (!is_string($haystack)) {
            return 0;
        }
        if ($this->ignore_line_endings) {
            $haystack = $this->normalize_line_endings($haystack);
        }
        return strlen($haystack);
    }
    private function normalize_line_endings(string $string): string
    {
        return strtr($string, ["\r\n" => "\n", "\r" => "\n"]);
    }
}