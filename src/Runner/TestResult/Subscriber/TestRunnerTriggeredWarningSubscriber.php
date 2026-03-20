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
namespace Php_Unit\Test_Runner\Test_Result;

use Php_Unit\Event\Test_Runner\Warning_Triggered;
use Php_Unit\Event\Test_Runner\Warning_Triggered_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Runner_Triggered_Warning_Subscriber extends Subscriber implements Warning_Triggered_Subscriber
{
    public function notify(Warning_Triggered $event): void
    {
        $this->collector()->test_runner_triggered_warning($event);
    }
}