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
namespace Php_Unit\Framework\Mock_Object\Stub;

use function array_shift;
use function count;
use Php_Unit\Framework\Mock_Object\Invocation;
use Php_Unit\Framework\Mock_Object\No_More_Return_Values_Configured_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Consecutive_Calls implements Stub
{
    private readonly int $number_of_configured_return_values;
    /**
     * @param array<mixed> $stack
     */
    public function __construct(private array $stack)
    {
        $this->number_of_configured_return_values = count($this->stack);
    }
    /**
     * @throws NoMoreReturnValuesConfiguredException
     */
    public function invoke(Invocation $invocation): mixed
    {
        if ($this->stack === []) {
            throw new No_More_Return_Values_Configured_Exception($invocation, $this->number_of_configured_return_values);
        }
        $value = array_shift($this->stack);
        if ($value instanceof Stub) {
            return $value->invoke($invocation);
        }
        return $value;
    }
}