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
namespace Php_Unit\Runner\Deprecation_Collector;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Subscriber
{
    public function __construct(private readonly Collector|In_Isolation_Collector $collector)
    {
    }
    protected function collector(): Collector|In_Isolation_Collector
    {
        return $this->collector;
    }
}