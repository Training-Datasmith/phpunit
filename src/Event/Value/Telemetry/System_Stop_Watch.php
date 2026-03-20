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
namespace Php_Unit\Event\Telemetry;

use function hrtime;
use Php_Unit\Event\InvalidArgumentException;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class System_Stop_Watch implements Stop_Watch
{
    /**
     * @throws InvalidArgumentException
     */
    public function current(): Hr_Time
    {
        return Hr_Time::from_seconds_and_nanoseconds(...hrtime());
    }
}