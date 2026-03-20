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
namespace Php_Unit\Logging\Test_Dox;

use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Deprecation_Triggered_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Triggered_Deprecation_Subscriber extends Subscriber implements Deprecation_Triggered_Subscriber
{
    public function notify(Deprecation_Triggered $event): void
    {
        $this->collector()->test_triggered_deprecation($event);
    }
}