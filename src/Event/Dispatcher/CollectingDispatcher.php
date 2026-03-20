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
namespace Php_Unit\Event;

use Php_Unit\Runner\Deprecation_Collector\Facade as DeprecationCollector;
use Php_Unit\Runner\Deprecation_Collector\Test_Triggered_Deprecation_Subscriber;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collecting_Dispatcher implements Dispatcher
{
    private Event_Collection $events;
    public function __construct(private readonly Direct_Dispatcher $isolated_direct_dispatcher)
    {
        $this->events = new Event_Collection();
        $this->isolated_direct_dispatcher->register_subscriber(new Test_Triggered_Deprecation_Subscriber(Deprecation_Collector::collector()));
    }
    public function dispatch(Event $event): void
    {
        $this->events->add($event);
        try {
            $this->isolated_direct_dispatcher->dispatch($event);
        } catch (Unknown_Event_Type_Exception) {
            // Do nothing.
        }
    }
    public function flush(): Event_Collection
    {
        $events = $this->events;
        $this->events = new Event_Collection();
        return $events;
    }
}