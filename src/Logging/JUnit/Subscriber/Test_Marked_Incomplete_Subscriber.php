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

use Php_Unit\Event\InvalidArgumentException;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Marked_Incomplete_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Marked_Incomplete_Subscriber extends Subscriber implements Marked_Incomplete_Subscriber
{
    /**
     * @throws InvalidArgumentException
     */
    public function notify(Marked_Incomplete $event): void
    {
        $this->logger()->test_marked_incomplete($event);
    }
}