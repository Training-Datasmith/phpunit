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

use Php_Unit\Event\Test_Suite\Started;
use Php_Unit\Event\Test_Suite\Started_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Suite_Started_Subscriber extends Subscriber implements Started_Subscriber
{
    public function notify(Started $event): void
    {
        $this->collector()->test_suite_started($event);
    }
}