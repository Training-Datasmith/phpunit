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

namespace PHPUnit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class WithEnvironmentVariable extends Metadata
{
    /**
     * @param non-empty-string $environmentVariableName
     */
    protected function __construct(Level $level, private string $environmentVariableName, private null|string $value)
    {
        parent::__construct($level);
    }

    public function isWithEnvironmentVariable(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function environmentVariableName(): string
    {
        return $this->environmentVariableName;
    }

    public function value(): null|string
    {
        return $this->value;
    }
}
