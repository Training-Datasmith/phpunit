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
namespace Php_Unit\Runner\Result_Cache;

use Php_Unit\Event\Test\Prepared;
use Php_Unit\Event\Test\Prepared_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Prepared_Subscriber extends Subscriber implements Prepared_Subscriber
{
    public function notify(Prepared $event): void
    {
        $this->handler()->test_prepared($event);
    }
}