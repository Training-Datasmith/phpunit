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

use function assert;
use const DIRECTORY_SEPARATOR;
use function explode;
use function implode;
use const PHP_EOL;
use Php_Unit\Framework\Exception as FrameworkException;
use function preg_last_error_msg;
use function preg_match;
use function preg_quote;
use function preg_replace;
use Sebastian_Bergmann\Diff\Differ;
use Sebastian_Bergmann\Diff\Output\Unified_Diff_Output_Builder;
use function sprintf;
use function strtr;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class String_Matches_Format_Description extends Constraint
{
    public function __construct(private readonly string $format_description)
    {
    }
    public function to_string(): string
    {
        return 'matches format description:' . PHP_EOL . $this->format_description;
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws FrameworkException
     */
    protected function matches(mixed $other): bool
    {
        $other = $this->convert_newlines($other);
        $matches = @preg_match($this->regular_expression_for_format_description($this->convert_newlines($this->format_description)), $other);
        if ($matches === false) {
            throw new Framework_Exception(sprintf('Format description cannot be matched: %s', preg_last_error_msg()));
        }
        return $matches > 0;
    }
    protected function failure_description(mixed $other): string
    {
        return 'string matches format description';
    }
    /**
     * Returns a cleaned up diff.
     *
     * The expected string can contain placeholders like %s and %d.
     * By using 'diff' such placeholders compared to the real output will
     * always be different, although we don't want to show them as different.
     * This method removes the expected differences by figuring out if a difference
     * is allowed by the use of a placeholder.
     *
     * The problem here are %A and %a multiline placeholders since we look at the
     * expected and actual output line by line. If differences allowed by those placeholders
     * stretch over multiple lines they will still end up in the final diff.
     * And since they mess up the line sync between the expected and actual output
     * all following allowed changes will not be detected/removed anymore.
     */
    protected function additional_failure_description(mixed $other): string
    {
        $from = explode("\n", $this->format_description);
        $to = explode("\n", $this->convert_newlines($other));
        foreach ($from as $index => $line) {
            // is the expected output line different from the actual output line
            if (isset($to[$index]) && $line !== $to[$index]) {
                $line = $this->regular_expression_for_format_description($line);
                // if the difference is allowed by a placeholder
                // overwrite the expected line with the actual line to prevent it from showing up in the diff
                if (preg_match($line, $to[$index]) > 0) {
                    $from[$index] = $to[$index];
                }
            }
        }
        $from = implode("\n", $from);
        $to = implode("\n", $to);
        return $this->differ()->diff($from, $to);
    }
    private function regular_expression_for_format_description(string $string): string
    {
        $string = strtr(preg_quote($string, '/'), ['%%' => '%', '%e' => preg_quote(DIRECTORY_SEPARATOR, '/'), '%s' => '[^\r\n]+', '%S' => '[^\r\n]*', '%a' => '.+?', '%A' => '.*?', '%w' => '\s*', '%i' => '[+-]?\d+', '%d' => '\d+', '%x' => '[0-9a-fA-F]+', '%f' => '[+-]?(?:\d+|(?=\.\d))(?:\.\d+)?(?:[Ee][+-]?\d+)?', '%c' => '.', '%0' => '\x00']);
        return '/^' . $string . '$/s';
    }
    private function convert_newlines(string $text): string
    {
        $result = preg_replace('/\r\n/', "\n", $text);
        assert($result !== null);
        return $result;
    }
    private function differ(): Differ
    {
        return new Differ(new Unified_Diff_Output_Builder("--- Expected\n+++ Actual\n"));
    }
}