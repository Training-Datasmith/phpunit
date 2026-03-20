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

use Sebastian_Bergmann\Type\Type;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Configurable_Method
{
    private Type $return_type;
    /**
     * @param non-empty-string  $name
     * @param array<int, mixed> $defaultParameterValues
     * @param non-negative-int  $numberOfParameters
     */
    public function __construct(private string $name, private array $default_parameter_values, private int $number_of_parameters, Type $return_type)
    {
        $this->return_type = $return_type;
    }
    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
    /**
     * @return array<int, mixed>
     */
    public function default_parameter_values(): array
    {
        return $this->default_parameter_values;
    }
    /**
     * @return non-negative-int
     */
    public function number_of_parameters(): int
    {
        return $this->number_of_parameters;
    }
    public function may_return(mixed $value): bool
    {
        return $this->return_type->is_assignable(Type::from_value($value, false));
    }
    public function return_type_declaration(): string
    {
        return $this->return_type->as_string();
    }
}