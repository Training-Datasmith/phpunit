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

use Php_Unit\Util\Filter;
use function sprintf;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Exception extends Constraint
{
    public function __construct(private readonly string $class_name)
    {
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return sprintf('exception of type "%s"', $this->class_name);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        return $other instanceof $this->class_name;
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @throws \PHPUnit\Framework\Exception
     */
    protected function failure_description(mixed $other): string
    {
        if ($other === null) {
            return sprintf('exception of type "%s" is thrown', $this->class_name);
        }
        $message = '';
        if ($other instanceof Throwable) {
            $message = '. Message was: "' . $other->get_message() . '" at' . "\n" . Filter::stack_trace_from_throwable_as_string($other);
        }
        return sprintf('exception of type "%s" matches expected exception "%s"%s', $other::class, $this->class_name, $message);
    }
}