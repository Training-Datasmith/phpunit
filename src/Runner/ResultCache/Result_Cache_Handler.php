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
namespace Php_Unit\Runner\Result_Cache;

use Php_Unit\Event\Event;
use Php_Unit\Event\Facade;
use Php_Unit\Event\Telemetry\Hr_Time;
use Php_Unit\Event\Test\Considered_Risky;
use Php_Unit\Event\Test\Errored;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Finished;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Prepared;
use Php_Unit\Event\Test\Skipped;
use Php_Unit\Framework\InvalidArgumentException;
use Php_Unit\Framework\Test_Status\Test_Status;
use function round;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Result_Cache_Handler
{
    private ?Hr_Time $time = null;
    private int $test_suite = 0;
    public function __construct(private readonly Result_Cache $cache, Facade $facade)
    {
        $this->register_subscribers($facade);
    }
    public function test_suite_started(): void
    {
        $this->test_suite++;
    }
    public function test_suite_finished(): void
    {
        $this->test_suite--;
        if ($this->test_suite === 0) {
            $this->cache->persist();
        }
    }
    public function test_prepared(Prepared $event): void
    {
        $this->time = $event->telemetry_info()->time();
    }
    public function test_marked_incomplete(Marked_Incomplete $event): void
    {
        $this->cache->set_status(Result_Cache_Id::from_test($event->test()), Test_Status::incomplete($event->throwable()->message()));
    }
    public function test_considered_risky(Considered_Risky $event): void
    {
        $this->cache->set_status(Result_Cache_Id::from_test($event->test()), Test_Status::risky($event->message()));
    }
    public function test_errored(Errored $event): void
    {
        $this->cache->set_status(Result_Cache_Id::from_test($event->test()), Test_Status::error($event->throwable()->message()));
    }
    public function test_failed(Failed $event): void
    {
        $this->cache->set_status(Result_Cache_Id::from_test($event->test()), Test_Status::failure($event->throwable()->message()));
    }
    /**
     * @throws \PHPUnit\Event\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function test_skipped(Skipped $event): void
    {
        $this->cache->set_status(Result_Cache_Id::from_test($event->test()), Test_Status::skipped($event->message()));
        $this->cache->set_time(Result_Cache_Id::from_test($event->test()), $this->duration($event));
    }
    /**
     * @throws \PHPUnit\Event\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function test_finished(Finished $event): void
    {
        $this->cache->set_time(Result_Cache_Id::from_test($event->test()), $this->duration($event));
        $this->time = null;
    }
    /**
     * @throws \PHPUnit\Event\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    private function duration(Event $event): float
    {
        if ($this->time === null) {
            return 0.0;
        }
        return round($event->telemetry_info()->time()->duration($this->time)->as_float(), 3);
    }
    private function register_subscribers(Facade $facade): void
    {
        $facade->register_subscribers(new Test_Suite_Started_Subscriber($this), new Test_Suite_Finished_Subscriber($this), new Test_Prepared_Subscriber($this), new Test_Marked_Incomplete_Subscriber($this), new Test_Considered_Risky_Subscriber($this), new Test_Errored_Subscriber($this), new Test_Failed_Subscriber($this), new Test_Skipped_Subscriber($this), new Test_Finished_Subscriber($this));
    }
}