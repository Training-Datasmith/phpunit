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
namespace Php_Unit\Util;

use function debug_backtrace;
use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const DEBUG_BACKTRACE_PROVIDE_OBJECT;
use Php_Unit\Event\Code\No_Test_Case_Object_On_Call_Stack_Exception;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\Parser\Registry;
use ReflectionMethod;
use function str_starts_with;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test
{
    /**
     * @throws NoTestCaseObjectOnCallStackException
     */
    public static function current_test_case(): Test_Case
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['object']) && $frame['object'] instanceof Test_Case) {
                return $frame['object'];
            }
        }
        throw new No_Test_Case_Object_On_Call_Stack_Exception();
    }
    public static function is_test_method(ReflectionMethod $method): bool
    {
        if (!$method->is_public()) {
            return false;
        }
        if (str_starts_with($method->get_name(), 'test')) {
            return true;
        }
        $metadata = Registry::parser()->for_method($method->get_declaring_class()->get_name(), $method->get_name());
        return $metadata->is_test()->is_not_empty();
    }
}