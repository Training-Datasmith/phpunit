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
namespace Php_Unit\Logging\J_Unit;

use Php_Unit\Event\Test\Printed_Unexpected_Output;
use Php_Unit\Event\Test\Printed_Unexpected_Output_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Printed_Unexpected_Output_Subscriber extends Subscriber implements Printed_Unexpected_Output_Subscriber
{
    public function notify(Printed_Unexpected_Output $event): void
    {
        $this->logger()->test_printed_unexpected_output($event);
    }
}