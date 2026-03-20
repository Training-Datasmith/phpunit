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

use function array_any;
use function array_key_exists;
use function class_exists;
use function class_implements;
use function in_array;
use function interface_exists;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Type_Map
{
    /**
     * @var array<class-string, class-string>
     */
    private array $mapping = [];
    /**
     * @param class-string $subscriberInterface
     * @param class-string $eventClass
     *
     * @throws EventAlreadyAssignedException
     * @throws InvalidEventException
     * @throws InvalidSubscriberException
     * @throws SubscriberTypeAlreadyRegisteredException
     * @throws UnknownEventException
     * @throws UnknownSubscriberException
     */
    public function add_mapping(string $subscriber_interface, string $event_class): void
    {
        $this->ensure_subscriber_interface_exists($subscriber_interface);
        $this->ensure_subscriber_interface_extends_interface($subscriber_interface);
        $this->ensure_event_class_exists($event_class);
        $this->ensure_event_class_implements_event_interface($event_class);
        $this->ensure_subscriber_was_not_already_registered($subscriber_interface);
        $this->ensure_event_was_not_already_assigned($event_class);
        $this->mapping[$subscriber_interface] = $event_class;
    }
    public function is_known_subscriber_type(Subscriber $subscriber): bool
    {
        return array_any(class_implements($subscriber), fn(string $interface): bool => array_key_exists($interface, $this->mapping));
    }
    public function is_known_event_type(Event $event): bool
    {
        return in_array($event::class, $this->mapping, true);
    }
    /**
     * @throws MapError
     *
     * @return class-string
     */
    public function map(Subscriber $subscriber): string
    {
        foreach (class_implements($subscriber) as $interface) {
            if (array_key_exists($interface, $this->mapping)) {
                return $this->mapping[$interface];
            }
        }
        throw new Map_Error(sprintf('Subscriber "%s" does not implement a known interface', $subscriber::class));
    }
    /**
     * @param class-string $subscriberInterface
     *
     * @throws UnknownSubscriberException
     */
    private function ensure_subscriber_interface_exists(string $subscriber_interface): void
    {
        if (!interface_exists($subscriber_interface)) {
            throw new Unknown_Subscriber_Exception(sprintf('Subscriber "%s" does not exist or is not an interface', $subscriber_interface));
        }
    }
    /**
     * @param class-string $eventClass
     *
     * @throws UnknownEventException
     */
    private function ensure_event_class_exists(string $event_class): void
    {
        if (!class_exists($event_class)) {
            throw new Unknown_Event_Exception(sprintf('Event class "%s" does not exist', $event_class));
        }
    }
    /**
     * @param class-string $subscriberInterface
     *
     * @throws InvalidSubscriberException
     */
    private function ensure_subscriber_interface_extends_interface(string $subscriber_interface): void
    {
        if (!in_array(Subscriber::class, class_implements($subscriber_interface), true)) {
            throw new Invalid_Subscriber_Exception(sprintf('Subscriber "%s" does not extend Subscriber interface', $subscriber_interface));
        }
    }
    /**
     * @param class-string $eventClass
     *
     * @throws InvalidEventException
     */
    private function ensure_event_class_implements_event_interface(string $event_class): void
    {
        if (!in_array(Event::class, class_implements($event_class), true)) {
            throw new Invalid_Event_Exception(sprintf('Event "%s" does not implement Event interface', $event_class));
        }
    }
    /**
     * @param class-string $subscriberInterface
     *
     * @throws SubscriberTypeAlreadyRegisteredException
     */
    private function ensure_subscriber_was_not_already_registered(string $subscriber_interface): void
    {
        if (array_key_exists($subscriber_interface, $this->mapping)) {
            throw new Subscriber_Type_Already_Registered_Exception(sprintf('Subscriber type "%s" already registered', $subscriber_interface));
        }
    }
    /**
     * @param class-string $eventClass
     *
     * @throws EventAlreadyAssignedException
     */
    private function ensure_event_was_not_already_assigned(string $event_class): void
    {
        if (in_array($event_class, $this->mapping, true)) {
            throw new Event_Already_Assigned_Exception(sprintf('Event "%s" already assigned', $event_class));
        }
    }
}