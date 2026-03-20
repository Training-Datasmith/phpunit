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

use Php_Unit\Util\Exporter;
use function sprintf;
use function str_contains;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Exception_Message_Is_Or_Contains extends Constraint
{
    public function __construct(private readonly string $expected_message)
    {
    }
    public function to_string(): string
    {
        if ($this->expected_message === '') {
            return 'exception message is empty';
        }
        return 'exception message contains ' . Exporter::export($this->expected_message);
    }
    protected function matches(mixed $other): bool
    {
        if ($this->expected_message === '') {
            return $other === '';
        }
        return str_contains((string) $other, $this->expected_message);
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failure_description(mixed $other): string
    {
        if ($this->expected_message === '') {
            return sprintf("exception message is empty but is '%s'", $other);
        }
        return sprintf("exception message '%s' contains '%s'", $other, $this->expected_message);
    }
}