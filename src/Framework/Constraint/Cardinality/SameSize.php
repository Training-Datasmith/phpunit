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

use Countable;
use Php_Unit\Framework\Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Same_Size extends Count
{
    /**
     * @param Countable|iterable<mixed> $expected
     *
     * @throws Exception
     */
    public function __construct(Countable|iterable $expected)
    {
        parent::__construct((int) $this->get_count_of($expected));
    }
}