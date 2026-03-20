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
namespace Php_Unit\Framework\Constraint;

use function assert;
use function count;
use function is_bool;
use function is_object;
use Php_Unit\Framework\Actual_Value_Is_Not_An_Object_Exception;
use Php_Unit\Framework\Comparison_Method_Does_Not_Accept_Parameter_Type_Exception;
use Php_Unit\Framework\Comparison_Method_Does_Not_Declare_Bool_Return_Type_Exception;
use Php_Unit\Framework\Comparison_Method_Does_Not_Declare_Exactly_One_Parameter_Exception;
use Php_Unit\Framework\Comparison_Method_Does_Not_Declare_Parameter_Type_Exception;
use Php_Unit\Framework\Comparison_Method_Does_Not_Exist_Exception;
use ReflectionNamedType;
use Reflection_Object;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Object_Equals extends Constraint
{
    public function __construct(private readonly object $expected, private readonly string $method = 'equals')
    {
    }
    public function to_string(): string
    {
        return 'two objects are equal';
    }
    /**
     * @throws ActualValueIsNotAnObjectException
     * @throws ComparisonMethodDoesNotAcceptParameterTypeException
     * @throws ComparisonMethodDoesNotDeclareBoolReturnTypeException
     * @throws ComparisonMethodDoesNotDeclareExactlyOneParameterException
     * @throws ComparisonMethodDoesNotDeclareParameterTypeException
     * @throws ComparisonMethodDoesNotExistException
     */
    protected function matches(mixed $other): bool
    {
        if (!is_object($other)) {
            throw new Actual_Value_Is_Not_An_Object_Exception();
        }
        $object = new Reflection_Object($other);
        if (!$object->has_method($this->method)) {
            throw new Comparison_Method_Does_Not_Exist_Exception($other::class, $this->method);
        }
        $method = $object->get_method($this->method);
        if (!$method->has_return_type()) {
            throw new Comparison_Method_Does_Not_Declare_Bool_Return_Type_Exception($other::class, $this->method);
        }
        $return_type = $method->get_return_type();
        if (!$return_type instanceof ReflectionNamedType) {
            throw new Comparison_Method_Does_Not_Declare_Bool_Return_Type_Exception($other::class, $this->method);
        }
        if ($return_type->allows_null()) {
            throw new Comparison_Method_Does_Not_Declare_Bool_Return_Type_Exception($other::class, $this->method);
        }
        if ($return_type->get_name() !== 'bool') {
            throw new Comparison_Method_Does_Not_Declare_Bool_Return_Type_Exception($other::class, $this->method);
        }
        if ($method->get_number_of_parameters() !== 1 || $method->get_number_of_required_parameters() !== 1) {
            throw new Comparison_Method_Does_Not_Declare_Exactly_One_Parameter_Exception($other::class, $this->method);
        }
        assert(count($method->get_parameters()) > 0);
        $parameter = $method->get_parameters()[0];
        if (!$parameter->has_type()) {
            throw new Comparison_Method_Does_Not_Declare_Parameter_Type_Exception($other::class, $this->method);
        }
        $type = $parameter->get_type();
        if (!$type instanceof ReflectionNamedType) {
            throw new Comparison_Method_Does_Not_Declare_Parameter_Type_Exception($other::class, $this->method);
        }
        $type_name = $type->get_name();
        if ($type_name === 'self') {
            $type_name = $other::class;
        }
        if (!$this->expected instanceof $type_name) {
            throw new Comparison_Method_Does_Not_Accept_Parameter_Type_Exception($other::class, $this->method, $this->expected::class);
        }
        /** @phpstan-ignore method.dynamicName */
        $result = $other->{$this->method}($this->expected);
        assert(is_bool($result));
        return $result;
    }
    protected function failure_description(mixed $other): string
    {
        return $this->to_string();
    }
}