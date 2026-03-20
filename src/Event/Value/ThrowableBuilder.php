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

use Php_Unit\Event\No_Previous_Throwable_Exception;
use Php_Unit\Framework\Exception;
use Php_Unit\Util\Filter;
use Php_Unit\Util\Throwable_To_String_Mapper;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Throwable_Builder
{
    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    public static function from(\Throwable $t): Throwable
    {
        $previous = $t->get_previous();
        if ($previous !== null) {
            $previous = self::from($previous);
        }
        return new Throwable($t::class, $t->get_message(), Throwable_To_String_Mapper::map($t), Filter::stack_trace_from_throwable_as_string($t, false), $previous);
    }
}