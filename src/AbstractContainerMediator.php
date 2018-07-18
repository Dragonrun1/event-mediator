<?php
declare(strict_types=1);
/**
 * Contains AbstractContainerMediator class.
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
 * Class AbstractContainerMediator
 */
abstract class AbstractContainerMediator extends Mediator implements ContainerMediatorInterface
{
    /**
     * Add a service as an event listener.
     *
     * @param string     $eventName Name of the event the listener is being added for.
     * @param array      $listener  Listener to be added. ['containerID', 'method']
     * @param int|string $priority  Priority level for the added listener.
     *
     * @return ContainerMediatorInterface Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function addServiceListener(string $eventName, array $listener, $priority = 0): ContainerMediatorInterface
    {
        $this->checkEventName($eventName);
        $this->checkAllowedServiceListener($listener);
        $priority = $this->getActualPriority($eventName, $priority);
        if (\array_key_exists($eventName, $this->serviceListeners)
            && \array_key_exists($priority, $this->serviceListeners[$eventName])
            && \in_array($listener, $this->serviceListeners[$eventName][$priority], \true)
        ) {
            return $this;
        }
        $this->serviceListeners[$eventName][$priority][] = $listener;
        return $this;
    }
    /**
     * @param array $events
     *
     * @return ContainerMediatorInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function addServiceListenersByEventList(array $events): ContainerMediatorInterface
    {
        $this->walkEventList($events, [$this, 'addServiceListener']);
        return $this;
    }
    /**
     * Add a service as a subscriber to event(s).
     *
     * @param ServiceSubscriberInterface $sub Service subscriber to be added.
     *
     * @return ContainerMediatorInterface Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function addServiceSubscriber(ServiceSubscriberInterface $sub): ContainerMediatorInterface
    {
        return $this->addServiceListenersByEventList($sub->getServiceSubscribedEvents());
    }
    /**
     * @param string $eventName
     *
     * @return array
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function getListeners(string $eventName = ''): array
    {
        if (0 !== \count($this->serviceListeners)) {
            if ('' === $eventName) {
                $this->lazyLoadServices(\array_keys($this->serviceListeners));
            } elseif (\array_key_exists($eventName, $this->serviceListeners)) {
                $this->lazyLoadServices([$eventName]);
            }
        }
        return parent::getListeners($eventName);
    }
    /** @noinspection GenericObjectTypeUsageInspection */
    /**
     * This method is used any time the mediator need to get the actual instance
     * of the class for an event.
     *
     * Normal will only be called during actual trigger of an event since lazy
     * loading is used.
     *
     * @param string $serviceName
     *
     * @return object
     */
    abstract public function getServiceByName(string $serviceName);
    /**
     * Get a list of service listeners for an event.
     *
     * Note that if event name is empty all listeners will be returned. Any event subscribers are also included in the
     * list.
     *
     * @param string $eventName Name of the event the list of service listeners is needed for.
     *
     * @return array List of event service listeners or empty array if event is unknown or has no listeners or
     *               subscribers.
     * @throws \InvalidArgumentException
     */
    public function getServiceListeners(string $eventName = ''): array
    {
        $this->sortServiceListeners($eventName);
        if ('' !== $eventName) {
            return \array_key_exists($eventName, $this->serviceListeners) ? $this->serviceListeners[$eventName] : [];
        }
        return $this->serviceListeners;
    }
    /**
     * Remove a service as an event listener.
     *
     * @param string     $eventName Event name that listener is being removed from.
     * @param array      $listener  Service listener to be removed.
     * @param int|string $priority  Priority level for the to be removed listener.
     *
     * @return ContainerMediatorInterface Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function removeServiceListener(string $eventName, array $listener, $priority = 0): ContainerMediatorInterface
    {
        $this->checkEventName($eventName);
        if (!\array_key_exists($eventName, $this->serviceListeners)) {
            return $this;
        }
        $this->checkAllowedServiceListener($listener);
        if (\in_array($eventName, $this->loadedServices, \true)) {
            $this->removeListener($eventName, [$this->getServiceByName($listener[0]), $listener[1]], $priority);
        }
        /**
         * @var array      $priorities
         * @var int|string $atPriority
         * @var array      $listeners
         */
        if ('last' !== $priority) {
            $priorities = $this->serviceListeners[$eventName];
        } else {
            $priorities = \array_reverse($this->serviceListeners[$eventName], \true);
            $priority = 'first';
        }
        $isIntPriority = \is_int($priority);
        foreach ($priorities as $atPriority => $listeners) {
            if ($isIntPriority && $priority !== $atPriority) {
                continue;
            }
            $key = \array_search($listener, $listeners, \true);
            if (\false !== $key) {
                $this->bubbleUpUnsetServiceListener($eventName, $atPriority, $key);
                if ('first' === $priority) {
                    break;
                }
            }
        }
        return $this;
    }
    /**
     * @param array $events
     *
     * @return ContainerMediatorInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function removeServiceListenersByEventList(array $events): ContainerMediatorInterface
    {
        $this->walkEventList($events, [$this, 'removeServiceListener']);
        return $this;
    }
    /**
     * Remove a service subscriber from event(s).
     *
     * @param ServiceSubscriberInterface $sub Subscriber to be removed.
     *
     * @return ContainerMediatorInterface Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function removeServiceSubscriber(ServiceSubscriberInterface $sub): ContainerMediatorInterface
    {
        return $this->removeServiceListenersByEventList($sub->getServiceSubscribedEvents());
    }
    /**
     * This is used to bring in the service container that will be used.
     *
     * Though not required it would be considered best practice for this method
     * to create a new instance of the container when given null. Another good
     * practice is to call this method from the class constructor to allow
     * easier testing. For examples of both have a look at
     * PimpleContainerMediator.
     *
     * @see PimpleContainerMediator Container mediator implemented using Pimple.
     *
     * @param mixed $value The service container to be used.
     *
     * @return ContainerMediatorInterface Fluent interface.
     */
    abstract public function setServiceContainer($value = \null): ContainerMediatorInterface;
    /**
     * @param string     $eventName
     * @param string|int $priority
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    protected function getActualPriority(string $eventName, $priority): int
    {
        if (\is_int($priority)) {
            return $priority;
        }
        if (!\in_array($priority, ['first', 'last'], \true)) {
            $mess = 'Unknown priority was given only "first", "last", or integer may be used';
            throw new \InvalidArgumentException($mess);
        }
        $listenerM = parent::getActualPriority($eventName, $priority);
        if ($priority === 'first') {
            $serviceM = \array_key_exists($eventName, $this->serviceListeners)
                ? \max(\array_keys($this->serviceListeners[$eventName])) + 1 : 1;
            return ($listenerM > $serviceM) ? $listenerM : $serviceM;
        }
        $serviceM = \array_key_exists($eventName, $this->serviceListeners)
            ? \min(\array_keys($this->serviceListeners[$eventName])) - 1 : -1;
        return ($listenerM < $serviceM) ? $listenerM : $serviceM;
    }
    /**
     * @param string $eventName
     * @param int    $priority
     * @param int    $key
     */
    private function bubbleUpUnsetServiceListener(string $eventName, int $priority, int $key)
    {
        unset($this->serviceListeners[$eventName][$priority][$key]);
        // Remove empty priorities.
        if (0 === \count($this->serviceListeners[$eventName][$priority])) {
            unset($this->serviceListeners[$eventName][$priority]);
        }
        // Remove empty events.
        if (0 === \count($this->serviceListeners[$eventName])) {
            unset($this->serviceListeners[$eventName]);
            $key = (int)\array_search($eventName, $this->loadedServices, \true);
            unset($this->loadedServices[$key]);
        }
    }
    /**
     * @param $listener
     *
     * @throws \InvalidArgumentException
     */
    private function checkAllowedServiceListener($listener)
    {
        if (\is_array($listener) && 2 === \count($listener)) {
            list($containerID, $method) = $listener;
            if (!\is_string($method)) {
                $mess = \sprintf('Service listener method name MUST be a string, but was given %s', \gettype($method));
                throw new \InvalidArgumentException($mess);
            }
            if (!\is_string($containerID)) {
                $mess = \sprintf('Service listener container ID MUST be a string, but was given %s',
                    \gettype($containerID));
                throw new \InvalidArgumentException($mess);
            }
            if (!\ctype_print($method) || \false === \preg_match('%\w{1,}%', $method)) {
                $mess = 'Service listener method name format is invalid, was given ' . $method;
                throw new \InvalidArgumentException($mess);
            }
            // Also catches empty string.
            if (!\ctype_print($containerID)) {
                $mess = 'Using any non-printable characters in the container ID is NOT allowed';
                throw new \InvalidArgumentException($mess);
            }
            return;
        }
        $mess = 'Service listener form MUST be ["containerID", "methodName"]';
        throw new \InvalidArgumentException($mess);
    }
    /**
     * @param string[] $eventNames
     *
     * @return ContainerMediatorInterface Fluent interface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    private function lazyLoadServices(array $eventNames): ContainerMediatorInterface
    {
        foreach ($eventNames as $event) {
            if (!\in_array($event, $this->loadedServices, \true)) {
                $this->loadedServices[] = $event;
            }
            /**
             * @var array $priorities
             * @var int   $priority
             * @var array $listeners
             * @var array $listener
             */
            $priorities = $this->serviceListeners[$event];
            foreach ($priorities as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    $this->addListener($event, [$this->getServiceByName($listener[0]), $listener[1]], $priority);
                }
            }
        }
        return $this;
    }
    /**
     * @param string $eventName
     *
     * @return ContainerMediatorInterface Fluent Interface
     * @throws \InvalidArgumentException
     */
    private function sortServiceListeners(string $eventName): ContainerMediatorInterface
    {
        if (0 === \count($this->serviceListeners)) {
            return $this;
        }
        if ('' !== $eventName) {
            if (!\array_key_exists($eventName, $this->serviceListeners)) {
                return $this;
            }
            $eventNames = [$eventName];
        } else {
            \ksort($this->serviceListeners);
            $eventNames = \array_keys(parent::getListeners());
        }
        foreach ($eventNames as $anEvent) {
            \krsort($this->serviceListeners[$anEvent], \SORT_NUMERIC);
        }
        return $this;
    }
    /**
     * List of already loaded services.
     *
     * @var array $loadedServices
     */
    private $loadedServices = [];
    /**
     * Holds list of service listeners.
     *
     * {@internal Actual Generics-style notation would be:
     *      array<string,array<int,array<int,array<string>>>>
     *      or put another way:
     *      $serviceListeners[string eventName][int priority][int]= ["containerID", "methodName"];
     * }
     *
     * @var array $serviceListeners Holds the list of service listeners that will be lazy loaded when events are
     * triggered.
     */
    private $serviceListeners = [];
}
