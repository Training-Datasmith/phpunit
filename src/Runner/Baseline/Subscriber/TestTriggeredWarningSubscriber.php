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
namespace Php_Unit\Runner\Baseline;

use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Event\Test\Warning_Triggered_Subscriber;
use Php_Unit\Runner\File_Does_Not_Exist_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Triggered_Warning_Subscriber extends Subscriber implements Warning_Triggered_Subscriber
{
    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function notify(Warning_Triggered $event): void
    {
        $this->generator()->test_triggered_issue($event);
    }
}