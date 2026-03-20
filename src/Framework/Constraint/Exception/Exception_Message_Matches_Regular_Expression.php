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

use Exception;
use Php_Unit\Util\Exporter;
use function preg_match;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Exception_Message_Matches_Regular_Expression extends Constraint
{
    public function __construct(private readonly string $regular_expression)
    {
    }
    public function to_string(): string
    {
        return 'exception message matches ' . Exporter::export($this->regular_expression);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws Exception
     */
    protected function matches(mixed $other): bool
    {
        $match = @preg_match($this->regular_expression, (string) $other);
        if ($match === false) {
            throw new \Php_Unit\Framework\Exception(sprintf('Invalid expected exception message regular expression given: %s', $this->regular_expression));
        }
        return $match === 1;
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failure_description(mixed $other): string
    {
        return sprintf("exception message '%s' matches '%s'", $other, $this->regular_expression);
    }
}