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
namespace Php_Unit\Metadata\Version;

use Php_Unit\Util\Version_Comparison_Operator;
use function version_compare;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Comparison_Requirement extends Requirement
{
    public function __construct(private string $version, private Version_Comparison_Operator $operator)
    {
    }
    public function is_satisfied_by(string $version): bool
    {
        return version_compare($version, $this->version, $this->operator->as_string());
    }
    public function as_string(): string
    {
        return $this->operator->as_string() . ' ' . $this->version;
    }
}