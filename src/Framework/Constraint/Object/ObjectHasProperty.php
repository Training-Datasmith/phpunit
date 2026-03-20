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
use function is_object;
use Reflection_Object;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Object_Has_Property extends Constraint
{
    public function __construct(private readonly string $property_name)
    {
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return sprintf('has property "%s"', $this->property_name);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches(mixed $other): bool
    {
        if (!is_object($other)) {
            return false;
        }
        return (new Reflection_Object($other))->has_property($this->property_name);
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failure_description(mixed $other): string
    {
        if (is_object($other)) {
            return sprintf('object of class "%s" %s', $other::class, $this->to_string());
        }
        return sprintf('"%s" (%s) %s', $other, gettype($other), $this->to_string());
    }
}