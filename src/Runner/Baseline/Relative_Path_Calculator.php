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
namespace Php_Unit\Runner\Baseline;

use function array_fill;
use function array_merge;
use function array_slice;
use function assert;
use function count;
use function explode;
use function implode;
use function str_replace;
use function strpos;
use function substr;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see Copied from https://github.com/phpstan/phpstan-src/blob/1.10.33/src/File/ParentDirectoryRelativePathHelper.php
 */
final readonly class Relative_Path_Calculator
{
    /**
     * @param non-empty-string $baselineDirectory
     */
    public function __construct(private string $baseline_directory)
    {
    }
    /**
     * @param non-empty-string $filename
     *
     * @return non-empty-string
     */
    public function calculate(string $filename): string
    {
        $result = implode('/', $this->parts($filename));
        assert($result !== '');
        return $result;
    }
    /**
     * @param non-empty-string $filename
     *
     * @return list<non-empty-string>
     */
    public function parts(string $filename): array
    {
        $scheme_position = strpos($filename, '://');
        if ($scheme_position !== false) {
            $filename = substr($filename, $scheme_position + 3);
            assert($filename !== '');
        }
        $parent_parts = explode('/', trim(str_replace('\\', '/', $this->baseline_directory), '/'));
        $parent_parts_count = count($parent_parts);
        $filename_parts = explode('/', trim(str_replace('\\', '/', $filename), '/'));
        $filename_parts_count = count($filename_parts);
        $i = 0;
        for (; $i < $filename_parts_count; $i++) {
            if ($parent_parts_count < $i + 1) {
                break;
            }
            $parent_path = implode('/', array_slice($parent_parts, 0, $i + 1));
            $filename_path = implode('/', array_slice($filename_parts, 0, $i + 1));
            if ($parent_path !== $filename_path) {
                break;
            }
        }
        if ($i === 0) {
            return [$filename];
        }
        $dots_count = $parent_parts_count - $i;
        assert($dots_count >= 0);
        return array_merge(array_fill(0, $dots_count, '..'), array_slice($filename_parts, $i));
    }
}