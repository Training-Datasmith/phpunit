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

use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Native_Type;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Traversable_Contains_Only extends Constraint
{
    private readonly Constraint $constraint;
    public static function for_native_type(Native_Type $type): self
    {
        return new self(new Is_Type($type), $type->value);
    }
    /**
     * @param class-string $type
     */
    public static function for_class_or_interface(string $type): self
    {
        return new self(new Is_Instance_Of($type), $type);
    }
    private function __construct(Is_Instance_Of|Is_Type $constraint, private readonly string $type)
    {
        $this->constraint = $constraint;
    }
    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws ExpectationFailedException
     */
    public function evaluate(mixed $other, string $description = '', bool $return_result = false): bool
    {
        $success = true;
        foreach ($other as $item) {
            if (!$this->constraint->evaluate($item, '', true)) {
                $success = false;
                break;
            }
        }
        if (!$success && !$return_result) {
            $this->fail($other, $description);
        }
        return $success;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return 'contains only values of type "' . $this->type . '"';
    }
}