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
namespace Php_Unit\Text_Ui\Output\Default;

use Php_Unit\Event\Facade;
use Php_Unit\Event\Test\Printed_Unexpected_Output;
use Php_Unit\Event\Test\Printed_Unexpected_Output_Subscriber;
use Php_Unit\Text_Ui\Output\Printer;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Unexpected_Output_Printer implements Printed_Unexpected_Output_Subscriber
{
    public function __construct(private Printer $printer, Facade $facade)
    {
        $facade->register_subscriber($this);
    }
    public function notify(Printed_Unexpected_Output $event): void
    {
        $this->printer->print($event->output());
    }
}