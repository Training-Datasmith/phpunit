<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use function is_array;

use PHPUnit\Util\Exporter;

use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class TraversableContains extends Constraint
{
    public function __construct(private readonly mixed $value)
    {
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'contains ' . Exporter::export($this->value);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            '%s %s',
            is_array($other) ? 'an array' : 'a traversable',
            $this->toString(),
        );
    }

    protected function value(): mixed
    {
        return $this->value;
    }
}
