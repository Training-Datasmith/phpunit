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

namespace PHPUnit\Metadata\Version;

use PHPUnit\Util\VersionComparisonOperator;

use function version_compare;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ComparisonRequirement extends Requirement
{
    public function __construct(private string $version, private VersionComparisonOperator $operator)
    {
    }

    public function isSatisfiedBy(string $version): bool
    {
        return version_compare($version, $this->version, $this->operator->asString());
    }

    public function asString(): string
    {
        return $this->operator->asString() . ' ' . $this->version;
    }
}
