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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Deferring_Dispatcher implements Subscribable_Dispatcher
{
    private Event_Collection $events;
    private bool $recording = true;
    public function __construct(private readonly Subscribable_Dispatcher $dispatcher)
    {
        $this->events = new Event_Collection();
    }
    public function register_tracer(Tracer\Tracer $tracer): void
    {
        $this->dispatcher->register_tracer($tracer);
    }
    public function register_subscriber(Subscriber $subscriber): void
    {
        $this->dispatcher->register_subscriber($subscriber);
    }
    public function dispatch(Event $event): void
    {
        if ($this->recording) {
            $this->events->add($event);
            return;
        }
        $this->dispatcher->dispatch($event);
    }
    public function flush(): void
    {
        $this->recording = false;
        foreach ($this->events as $event) {
            $this->dispatcher->dispatch($event);
        }
        $this->events = new Event_Collection();
    }
}