<?php
declare(strict_types = 1);
/**
 * Contains Mediator class.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * which has minimal dependencies so it is easy to drop in and use.
 * Copyright (C) 2015-2016 Michael Cummings
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, you may write to
 *
 * Free Software Foundation, Inc.
 * 59 Temple Place, Suite 330
 * Boston, MA 02111-1307 USA
 *
 * or find a electronic copy at
 * <http://spdx.org/licenses/GPL-2.0.html>.
 *
 * You should also be able to find a copy of this license in the included
 * LICENSE file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2015-2016 Michael Cummings
 * @license   GPL-2.0
 */
namespace EventMediator;

/**
 * Class Mediator
 */
class Mediator implements MediatorInterface
{
    /**
     * @param string     $eventName
     * @param callable   $listener
     * @param int|string $priority
     *
     * @return MediatorInterface Fluent interface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function addListener(string $eventName, callable $listener, $priority = 0): MediatorInterface
    {
        $this->checkEventName($eventName);
        $priority = $this->getActualPriority($eventName, $priority);
        if (!(\array_key_exists($eventName, $this->listeners)
            && \array_key_exists($priority, $this->listeners[$eventName])
            && \in_array($listener, $this->listeners[$eventName][$priority], \true))
        ) {
            $this->listeners[$eventName][$priority][] = $listener;
        }
        return $this;
    }
    /**
     * @param array $events
     *
     * @return MediatorInterface Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function addListenersByEventList(array $events): MediatorInterface
    {
        $this->walkEventList($events, [$this, 'addListener']);
        return $this;
    }
    /**
     * @param SubscriberInterface $sub
     *
     * @return MediatorInterface Fluent interface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function addSubscriber(SubscriberInterface $sub): MediatorInterface
    {
        return $this->addListenersByEventList($sub->getSubscribedEvents());
    }
    /**
     * @param string $eventName
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getListeners(string $eventName = ''): array
    {
        $this->sortListeners($eventName);
        if ('' !== $eventName) {
            return \array_key_exists($eventName, $this->listeners) ? $this->listeners[$eventName] : [];
        }
        return $this->listeners;
    }
    /**
     * @param string $eventName
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function hasListeners(string $eventName = ''): bool
    {
        return (bool)$this->getListeners($eventName);
    }
    /**
     * @param string     $eventName
     * @param callable   $listener
     * @param int|string $priority
     *
     * @return MediatorInterface Fluent interface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function removeListener(string $eventName, callable $listener, $priority = 0): MediatorInterface
    {
        $this->checkEventName($eventName);
        if (!\array_key_exists($eventName, $this->listeners)) {
            return $this;
        }
        /**
         * @var array      $priorities
         * @var int        $atPriority
         * @var callable[] $listeners
         */
        if ('last' !== $priority) {
            $priorities = $this->listeners[$eventName];
        } else {
            $priorities = \array_reverse($this->listeners[$eventName], \true);
            $priority = 'first';
        }
        $isIntPriority = \is_int($priority);
        foreach ($priorities as $atPriority => $listeners) {
            if ($isIntPriority && $priority !== $atPriority) {
                continue;
            }
            $key = \array_search($listener, $listeners, \true);
            if (\false !== $key) {
                $this->bubbleUpUnsetListener($eventName, $atPriority, $key);
                if ('first' === $priority) {
                    break;
                }
            }
        }
        return $this;
    }
    /**
     * @param array $events Events to be removed.
     *
     * @return MediatorInterface Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function removeListenersByEventList(array $events): MediatorInterface
    {
        $this->walkEventList($events, [$this, 'removeListener']);
        return $this;
    }
    /**
     * @param SubscriberInterface $sub
     *
     * @return MediatorInterface Fluent interface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function removeSubscriber(SubscriberInterface $sub): MediatorInterface
    {
        return $this->removeListenersByEventList($sub->getSubscribedEvents());
    }
    /**
     * @param string              $eventName
     * @param EventInterface|null $event
     *
     * @return EventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function trigger(string $eventName, EventInterface $event = \null): EventInterface
    {
        $this->checkEventName($eventName);
        if (\null === $event) {
            $event = new Event();
        }
        $priorities = $this->getListeners($eventName);
        if (0 !== \count($priorities)) {
            /**
             * @var array    $listeners
             * @var callable $listener
             */
            foreach ($priorities as $listeners) {
                foreach ($listeners as $listener) {
                    $listener($event, $eventName, $this);
                    /** @noinspection DisconnectedForeachInstructionInspection */
                    if ($event->hasBeenHandled()) {
                        break 2;
                    }
                }
            }
        }
        return $event;
    }
    /**
     * @param $eventName
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    protected function checkEventName(string $eventName)
    {
        if ('' === $eventName) {
            $mess = 'Event name can NOT be empty';
            throw new \DomainException($mess);
        }
        if (!\ctype_print($eventName)) {
            $mess = 'Using any non-printable characters in the event name is NOT allowed';
            throw new \DomainException($mess);
        }
    }
    /**
     * @param string     $eventName
     * @param string|int $priority
     *
     * @return int
     */
    protected function getActualPriority(string $eventName, $priority): int
    {
        if ($priority === 'first') {
            $priority = !empty($this->listeners[$eventName]) ? \max(\array_keys($this->listeners[$eventName])) + 1 : 1;
            return $priority;
        }
        if ($priority === 'last') {
            $priority = !empty($this->listeners[$eventName]) ? \min(\array_keys($this->listeners[$eventName])) - 1 : -1;
            return $priority;
        }
        return $priority;
    }
    /**
     * @param array    $events
     * @param callable $callback
     *
     * @throws \LengthException
     */
    protected function walkEventList(array $events, callable $callback)
    {
        if (0 === \count($events)) {
            return;
        }
        /**
         * @var string     $eventName
         * @var array      $priorities
         * @var int|string $priority
         * @var array      $listeners
         * @var array      $listener
         */
        foreach ($events as $eventName => $priorities) {
            if (!\is_array($priorities) || 0 === \count($priorities)) {
                $mess = 'Must have as least one priority per listed event';
                throw new \LengthException($mess);
            }
            foreach ($priorities as $priority => $listeners) {
                if (!\is_array($listeners) || 0 === \count($listeners)) {
                    $mess = 'Must have at least one listener per listed priority';
                    throw new \LengthException($mess);
                }
                foreach ($listeners as $listener) {
                    $callback($eventName, $listener, $priority);
                }
            }
        }
    }
    /**
     * @param string $eventName
     * @param int    $priority
     * @param int    $key
     */
    private function bubbleUpUnsetListener(string $eventName, int $priority, int $key)
    {
        unset($this->listeners[$eventName][$priority][$key]);
        if (0 === \count($this->listeners[$eventName][$priority])) {
            unset($this->listeners[$eventName][$priority]);
        }
        if (0 === \count($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }
    /**
     * @param string $eventName
     *
     * @return MediatorInterface Fluent interface
     * @throws \InvalidArgumentException
     */
    private function sortListeners(string $eventName = ''): MediatorInterface
    {
        if (0 === \count($this->listeners)) {
            return $this;
        }
        if ('' !== $eventName) {
            if (!\array_key_exists($eventName, $this->listeners)) {
                return $this;
            }
            $eventNames = [$eventName];
        } else {
            \ksort($this->listeners);
            $eventNames = \array_keys($this->listeners);
        }
        foreach ($eventNames as $anEvent) {
            \krsort($this->listeners[$anEvent], \SORT_NUMERIC);
        }
        return $this;
    }
    /**
     * @var array $listeners
     */
    private $listeners = [];
}
