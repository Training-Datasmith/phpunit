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
namespace Php_Unit\Runner\Garbage_Collection;

use function gc_collect_cycles;
use function gc_disable;
use function gc_enable;
use Php_Unit\Event\Facade;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Garbage_Collection_Handler
{
    private int $tests = 0;
    public function __construct(private readonly Facade $facade, private readonly int $threshold)
    {
        $this->register_subscribers();
    }
    public function execution_started(): void
    {
        gc_disable();
        $this->facade->emitter()->test_runner_disabled_garbage_collection();
        gc_collect_cycles();
        $this->facade->emitter()->test_runner_triggered_garbage_collection();
    }
    public function execution_finished(): void
    {
        gc_collect_cycles();
        $this->facade->emitter()->test_runner_triggered_garbage_collection();
        gc_enable();
        $this->facade->emitter()->test_runner_enabled_garbage_collection();
    }
    public function test_finished(): void
    {
        $this->tests++;
        if ($this->tests === $this->threshold) {
            gc_collect_cycles();
            $this->facade->emitter()->test_runner_triggered_garbage_collection();
            $this->tests = 0;
        }
    }
    private function register_subscribers(): void
    {
        $this->facade->register_subscribers(new Execution_Started_Subscriber($this), new Execution_Finished_Subscriber($this), new Test_Finished_Subscriber($this));
    }
}