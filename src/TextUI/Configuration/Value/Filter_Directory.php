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
namespace Php_Unit\Text_Ui\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Filter_Directory
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(private string $path, private string $prefix, private string $suffix, private bool $include_in_code_coverage = true)
    {
    }
    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }
    public function prefix(): string
    {
        return $this->prefix;
    }
    public function suffix(): string
    {
        return $this->suffix;
    }
    public function include_in_code_coverage(): bool
    {
        return $this->include_in_code_coverage;
    }
}