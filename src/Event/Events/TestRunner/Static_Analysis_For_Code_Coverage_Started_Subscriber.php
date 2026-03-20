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
namespace Php_Unit\Event\Test_Runner;

use Php_Unit\Event\Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface Static_Analysis_For_Code_Coverage_Started_Subscriber extends Subscriber
{
    public function notify(Static_Analysis_For_Code_Coverage_Started $event): void;
}