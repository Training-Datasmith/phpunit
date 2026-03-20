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

use function gettype;
use function is_array;
use function is_bool;
use function is_callable;
use function is_float;
use function is_int;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_scalar;
use function is_string;
use Php_Unit\Framework\Native_Type;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Is_Type extends Constraint
{
    public function __construct(private readonly Native_Type $type)
    {
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return sprintf('is of type %s', $this->type->value);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        switch ($this->type) {
            case Native_Type::Numeric:
                return is_numeric($other);
            case Native_Type::Int:
                return is_int($other);
            case Native_Type::Float:
                return is_float($other);
            case Native_Type::String:
                return is_string($other);
            case Native_Type::Bool:
                return is_bool($other);
            case Native_Type::Null:
                return null === $other;
            case Native_Type::Array:
                return is_array($other);
            case Native_Type::Object:
                return is_object($other);
            case Native_Type::Resource:
                $type = gettype($other);
                return $type === 'resource' || $type === 'resource (closed)';
            case Native_Type::ClosedResource:
                return gettype($other) === 'resource (closed)';
            case Native_Type::Scalar:
                return is_scalar($other);
            case Native_Type::Callable:
                return is_callable($other);
            case Native_Type::Iterable:
                return is_iterable($other);
            default:
                return false;
        }
    }
}