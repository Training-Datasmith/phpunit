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
namespace Php_Unit\Logging\Team_City;

use Php_Unit\Event\InvalidArgumentException;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Failed_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Failed_Subscriber extends Subscriber implements Failed_Subscriber
{
    /**
     * @throws InvalidArgumentException
     */
    public function notify(Failed $event): void
    {
        $this->logger()->test_failed($event);
    }
}