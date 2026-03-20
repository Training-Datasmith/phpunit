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

use Php_Unit\Framework\Mock_Object\Invocation;
use Php_Unit\Framework\Mock_Object\RuntimeException;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Return_Self implements Stub
{
    /**
     * @throws RuntimeException
     */
    public function invoke(Invocation $invocation): object
    {
        return $invocation->object();
    }
}