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

use Php_Unit\Event\Test\After_Last_Test_Method_Errored;
use Php_Unit\Event\Test\After_Last_Test_Method_Errored_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class After_Test_Class_Method_Errored_Subscriber extends Subscriber implements After_Last_Test_Method_Errored_Subscriber
{
    public function notify(After_Last_Test_Method_Errored $event): void
    {
        $this->collector()->after_test_class_method_errored($event);
    }
}