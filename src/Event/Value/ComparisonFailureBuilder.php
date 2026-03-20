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
namespace Php_Unit\Event\Code;

use function is_bool;
use function is_scalar;
use Php_Unit\Framework\Expectation_Failed_Exception;
use function print_r;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Comparison_Failure_Builder
{
    public static function from(Throwable $t): ?Comparison_Failure
    {
        if (!$t instanceof Expectation_Failed_Exception) {
            return null;
        }
        if ($t->get_comparison_failure() === null) {
            return null;
        }
        $expected_as_string = $t->get_comparison_failure()->get_expected_as_string();
        if ($expected_as_string === '') {
            $expected_as_string = self::map_scalar_value_to_string($t->get_comparison_failure()->get_expected());
        }
        $actual_as_string = $t->get_comparison_failure()->get_actual_as_string();
        if ($actual_as_string === '') {
            $actual_as_string = self::map_scalar_value_to_string($t->get_comparison_failure()->get_actual());
        }
        return new Comparison_Failure($expected_as_string, $actual_as_string, $t->get_comparison_failure()->get_diff());
    }
    private static function map_scalar_value_to_string(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return print_r($value, true);
        }
        return '';
    }
}