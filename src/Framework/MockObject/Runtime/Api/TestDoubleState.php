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
namespace Php_Unit\Framework\Mock_Object;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Double_State
{
    private ?Invocation_Handler $invocation_handler = null;
    /**
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public function __construct(private readonly array $configurable_methods, private readonly bool $generate_return_values, private readonly bool $is_mock_object = false)
    {
    }
    public function invocation_handler(): Invocation_Handler
    {
        if ($this->invocation_handler !== null) {
            return $this->invocation_handler;
        }
        $this->invocation_handler = new Invocation_Handler($this->configurable_methods, $this->generate_return_values, $this->is_mock_object);
        return $this->invocation_handler;
    }
    public function clone_invocation_handler(): void
    {
        if ($this->invocation_handler === null) {
            return;
        }
        $this->invocation_handler = clone $this->invocation_handler;
    }
    public function unset_invocation_handler(): void
    {
        $this->invocation_handler = null;
    }
    /**
     * @return list<ConfigurableMethod>
     */
    public function configurable_methods(): array
    {
        return $this->configurable_methods;
    }
    public function generate_return_values(): bool
    {
        return $this->generate_return_values;
    }
}