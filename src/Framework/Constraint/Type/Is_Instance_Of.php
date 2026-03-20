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

use function class_exists;
use function interface_exists;
use Php_Unit\Framework\Unknown_Class_Or_Interface_Exception;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Is_Instance_Of extends Constraint
{
    /**
     * @var class-string
     */
    private readonly string $name;
    /**
     * @var 'class'|'interface'
     */
    private readonly string $type;
    /**
     * @throws UnknownClassOrInterfaceException
     */
    public function __construct(string $name)
    {
        if (class_exists($name)) {
            $this->type = 'class';
        } elseif (interface_exists($name)) {
            $this->type = 'interface';
        } else {
            throw new Unknown_Class_Or_Interface_Exception($name);
        }
        $this->name = $name;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return sprintf('is an instance of %s %s', $this->type, $this->name);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        return $other instanceof $this->name;
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failure_description(mixed $other): string
    {
        return $this->value_to_type_string_fragment($other) . $this->to_string();
    }
}