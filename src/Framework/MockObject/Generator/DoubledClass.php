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

use function class_exists;
use Php_Unit\Framework\Mock_Object\Configurable_Method;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Doubled_Class
{
    /**
     * @param class-string             $mockName
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public function __construct(private string $class_code, private string $mock_name, private array $configurable_methods)
    {
    }
    /**
     * @return class-string
     */
    public function generate(): string
    {
        if (!class_exists($this->mock_name, false)) {
            eval($this->class_code);
        }
        return $this->mock_name;
    }
    public function class_code(): string
    {
        return $this->class_code;
    }
    /**
     * @return list<ConfigurableMethod>
     */
    public function configurable_methods(): array
    {
        return $this->configurable_methods;
    }
}