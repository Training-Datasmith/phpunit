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

use function count;
use Countable;
use IteratorAggregate;
/**
 * @template-implements IteratorAggregate<non-negative-int, Event>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Event_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<Event>
     */
    private array $events = [];
    public function add(Event ...$events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;
        }
    }
    /**
     * @return list<Event>
     */
    public function as_array(): array
    {
        return $this->events;
    }
    public function count(): int
    {
        return count($this->events);
    }
    public function is_empty(): bool
    {
        return $this->count() === 0;
    }
    public function is_not_empty(): bool
    {
        return $this->count() > 0;
    }
    public function getIterator(): Event_Collection_Iterator
    {
        return new Event_Collection_Iterator($this);
    }
}