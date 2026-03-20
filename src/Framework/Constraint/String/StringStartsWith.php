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

use Php_Unit\Framework\Empty_String_Exception;
use function str_starts_with;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class String_Starts_With extends Constraint
{
    private readonly string $prefix;
    /**
     * @throws EmptyStringException
     */
    public function __construct(string $prefix)
    {
        if ($prefix === '') {
            throw new Empty_String_Exception();
        }
        $this->prefix = $prefix;
    }
    /**
     * Returns a string representation of the constraint.
     */
    public function to_string(): string
    {
        return 'starts with "' . $this->prefix . '"';
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        return str_starts_with((string) $other, $this->prefix);
    }
}