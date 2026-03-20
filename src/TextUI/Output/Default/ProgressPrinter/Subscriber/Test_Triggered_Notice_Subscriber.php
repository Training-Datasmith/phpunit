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
namespace Php_Unit\Text_Ui\Output\Default\Progress_Printer;

use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Notice_Triggered_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Triggered_Notice_Subscriber extends Subscriber implements Notice_Triggered_Subscriber
{
    public function notify(Notice_Triggered $event): void
    {
        $this->printer()->test_triggered_notice($event);
    }
}