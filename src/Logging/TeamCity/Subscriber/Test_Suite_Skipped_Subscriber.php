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
namespace Php_Unit\Logging\Team_City;

use Php_Unit\Event\InvalidArgumentException;
use Php_Unit\Event\Test_Suite\Skipped;
use Php_Unit\Event\Test_Suite\Skipped_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Suite_Skipped_Subscriber extends Subscriber implements Skipped_Subscriber
{
    /**
     * @throws InvalidArgumentException
     */
    public function notify(Skipped $event): void
    {
        $this->logger()->test_suite_skipped($event);
    }
}