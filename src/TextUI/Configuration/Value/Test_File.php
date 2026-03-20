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

use Php_Unit\Util\Version_Comparison_Operator;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Test_File
{
    /**
     * @param non-empty-string       $path
     * @param list<non-empty-string> $groups
     */
    public function __construct(private string $path, private string $php_version, private Version_Comparison_Operator $php_version_operator, private array $groups)
    {
    }
    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }
    public function php_version(): string
    {
        return $this->php_version;
    }
    public function php_version_operator(): Version_Comparison_Operator
    {
        return $this->php_version_operator;
    }
    /**
     * @return list<non-empty-string>
     */
    public function groups(): array
    {
        return $this->groups;
    }
}