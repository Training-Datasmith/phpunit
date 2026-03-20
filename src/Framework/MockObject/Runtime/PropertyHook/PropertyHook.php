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
namespace Php_Unit\Framework\Mock_Object\Runtime;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Property_Hook
{
    /**
     * @param non-empty-string $propertyName
     */
    public static function get(string $property_name): Property_Get_Hook
    {
        return new Property_Get_Hook($property_name);
    }
    /**
     * @param non-empty-string $propertyName
     */
    public static function set(string $property_name): Property_Set_Hook
    {
        return new Property_Set_Hook($property_name);
    }
    /**
     * @param non-empty-string $propertyName
     */
    protected function __construct(private string $property_name)
    {
    }
    /**
     * @return non-empty-string
     */
    public function property_name(): string
    {
        return $this->property_name;
    }
    /**
     * @return non-empty-string
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    abstract public function as_string(): string;
}