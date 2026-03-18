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

use SebastianBergmann\Type\Type;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class HookedProperty
{
    private Type $type;

    /**
     * @param non-empty-string $name
     */
    public function __construct(private string $name, Type $type, private bool $getHook, private bool $setHook, private ?Type $setterType)
    {
        $this->type       = $type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function hasGetHook(): bool
    {
        return $this->getHook;
    }

    public function hasSetHook(): bool
    {
        return $this->setHook;
    }

    /**
     * @throws RuntimeException
     */
    public function setterType(): Type
    {
        if ($this->setterType === null) {
            throw new RuntimeException();
        }

        return $this->setterType;
    }
}
