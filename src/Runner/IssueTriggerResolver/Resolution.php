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
namespace Php_Unit\Runner\Issue_Trigger_Resolver;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Resolution
{
    public function __construct(private ?string $callee, private ?string $caller)
    {
    }
    /**
     * @phpstan-assert-if-true !null $this->callee
     */
    public function has_callee(): bool
    {
        return $this->callee !== null;
    }
    public function callee(): ?string
    {
        return $this->callee;
    }
    /**
     * @phpstan-assert-if-true !null $this->caller
     */
    public function has_caller(): bool
    {
        return $this->caller !== null;
    }
    public function caller(): ?string
    {
        return $this->caller;
    }
}