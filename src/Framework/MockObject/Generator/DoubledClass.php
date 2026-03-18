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

namespace PHPUnit\Framework\MockObject\Generator;

use function class_exists;

use PHPUnit\Framework\MockObject\ConfigurableMethod;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DoubledClass
{
    /**
     * @param class-string             $mockName
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public function __construct(private string $classCode, private string $mockName, private array $configurableMethods)
    {
    }

    /**
     * @return class-string
     */
    public function generate(): string
    {
        if (!class_exists($this->mockName, false)) {
            eval($this->classCode);
        }

        return $this->mockName;
    }

    public function classCode(): string
    {
        return $this->classCode;
    }

    /**
     * @return list<ConfigurableMethod>
     */
    public function configurableMethods(): array
    {
        return $this->configurableMethods;
    }
}
