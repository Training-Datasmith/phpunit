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

use Php_Unit\Framework\Expectation_Failed_Exception;
use Php_Unit\Framework\Phpt_Assertion_Failed_Error;
use Php_Unit\Framework\Self_Describing;
use Php_Unit\Runner\ErrorException;
use Throwable;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Throwable_To_String_Mapper
{
    public static function map(Throwable $t): string
    {
        if ($t instanceof ErrorException) {
            return $t->get_message();
        }
        if ($t instanceof Self_Describing) {
            $buffer = $t->to_string();
            if ($t instanceof Expectation_Failed_Exception && $t->get_comparison_failure() !== null) {
                $buffer .= $t->get_comparison_failure()->get_diff();
            }
            if ($t instanceof Phpt_Assertion_Failed_Error) {
                $buffer .= $t->diff();
            }
            if ($buffer !== '') {
                return trim($buffer) . "\n";
            }
            return $buffer;
        }
        return $t::class . ': ' . $t->get_message() . "\n";
    }
}