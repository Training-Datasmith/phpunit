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

use function array_key_exists;
use function dirname;
use const PHP_EOL;
use function sprintf;
use function str_starts_with;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Direct_Dispatcher implements Subscribable_Dispatcher
{
    /**
     * @var array<class-string, list<Subscriber>>
     */
    private array $subscribers = [];
    /**
     * @var list<Tracer\Tracer>
     */
    private array $tracers = [];
    public function __construct(private readonly Type_Map $type_map)
    {
    }
    public function register_tracer(Tracer\Tracer $tracer): void
    {
        $this->tracers[] = $tracer;
    }
    /**
     * @throws MapError
     * @throws UnknownSubscriberTypeException
     */
    public function register_subscriber(Subscriber $subscriber): void
    {
        if (!$this->type_map->is_known_subscriber_type($subscriber)) {
            throw new Unknown_Subscriber_Type_Exception(sprintf('Subscriber "%s" does not implement any known interface - did you forget to register it?', $subscriber::class));
        }
        $event_class_name = $this->type_map->map($subscriber);
        if (!array_key_exists($event_class_name, $this->subscribers)) {
            $this->subscribers[$event_class_name] = [];
        }
        $this->subscribers[$event_class_name][] = $subscriber;
    }
    /**
     * @throws Throwable
     * @throws UnknownEventTypeException
     */
    public function dispatch(Event $event): void
    {
        $event_class_name = $event::class;
        if (!$this->type_map->is_known_event_type($event)) {
            throw new Unknown_Event_Type_Exception(sprintf('Unknown event type "%s"', $event_class_name));
        }
        foreach ($this->tracers as $tracer) {
            try {
                $tracer->trace($event);
                // @codeCoverageIgnoreStart
            } catch (Throwable $t) {
                $this->handle_throwable($t);
            }
            // @codeCoverageIgnoreEnd
        }
        if (!array_key_exists($event_class_name, $this->subscribers)) {
            return;
        }
        foreach ($this->subscribers[$event_class_name] as $subscriber) {
            try {
                /** @phpstan-ignore method.notFound */
                $subscriber->notify($event);
            } catch (Throwable $t) {
                $this->handle_throwable($t);
            }
        }
    }
    /**
     * @throws Throwable
     */
    public function handle_throwable(Throwable $t): void
    {
        if ($this->is_throwable_from_third_party_subscriber($t)) {
            Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Exception in third-party event subscriber: %s%s%s', $t->get_message(), PHP_EOL, $t->get_trace_as_string()));
            return;
        }
        // @codeCoverageIgnoreStart
        throw $t;
        // @codeCoverageIgnoreEnd
    }
    private function is_throwable_from_third_party_subscriber(Throwable $t): bool
    {
        return !str_starts_with($t->get_file(), dirname(__DIR__, 2));
    }
}