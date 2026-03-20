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
namespace Php_Unit\Framework\Mock_Object\Generator;

use function array_key_exists;
use function array_values;
use function strtolower;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Doubled_Method_Set
{
    /**
     * @var array<string,DoubledMethod>
     */
    private array $methods = [];
    public function add_methods(Doubled_Method ...$methods): void
    {
        foreach ($methods as $method) {
            $this->methods[strtolower($method->method_name())] = $method;
        }
    }
    /**
     * @return list<DoubledMethod>
     */
    public function as_array(): array
    {
        return array_values($this->methods);
    }
    public function has_method(string $method_name): bool
    {
        return array_key_exists(strtolower($method_name), $this->methods);
    }
}