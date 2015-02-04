<?php
/**
 * Contains AbstractContainerMediator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * with minimum dependencies so it is easy to drop in and use.
 * Copyright (C) 2015 Michael Cummings
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should also be able to find a copy of this license in the included
 * LICENSE file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL-2.0
 * @author    Michael Cummings
 * <mgcummings@yahoo.com>
 */
namespace EventMediator;

use DomainException;
use InvalidArgumentException;
use LogicException;
use Pimple\Container;

/**
 * Class AbstractContainerMediator
 */
abstract class AbstractContainerMediator extends Mediator implements
    ContainerMediatorInterface
{
    /**
     * @inheritdoc
     */
    abstract public function setServiceContainer($value = null);
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function addServiceListener(
        $eventName,
        array $listener,
        $priority = 0
    ) {
        $this->checkEventName($eventName);
        $this->checkAllowedServiceListener($listener);
        $priority = $this->getActualPriority($eventName, $priority);
        if (array_key_exists($eventName, $this->serviceListeners)
            && array_key_exists($priority, $this->serviceListeners[$eventName])
        ) {
            $key = array_search(
                $listener,
                $this->serviceListeners[$eventName][$priority],
                true
            );
            if (false !== $key) {
                return $this;
            }
        }
        $this->serviceListeners[$eventName][$priority][] = $listener;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function addServiceSubscriber($serviceName, SubscriberInterface $sub)
    {
        /**
         * @type string|array $listeners
         */
        foreach ($sub->getSubscribedEvents() as $eventName => $listeners) {
            if (is_string($listeners)) {
                $this->addServiceListener(
                    $eventName,
                    [$serviceName, $listeners]
                );
                continue;
            }
            if (is_string($listeners[0])) {
                $this->addServiceListener(
                    $eventName,
                    [$serviceName, $listeners[0]],
                    array_key_exists(1, $listeners) ? $listeners[1] : 0
                );
            } elseif (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $this->addServiceListener(
                        $eventName,
                        [$serviceName, $listener[0]],
                        array_key_exists(1, $listener) ? $listener[1] : 0
                    );
                }
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function getListeners($eventName = '')
    {
        if (!is_string($eventName)) {
            $mess = sprintf(
                'Event name MUST be a string, but given %s',
                gettype($eventName)
            );
            throw new InvalidArgumentException($mess);
        }
        $this->lazyLoadServices($eventName)
             ->sortListeners($eventName);
        if ('' !== $eventName) {
            return array_key_exists($eventName, $this->listeners)
                ? $this->listeners[$eventName] : [];
        }
        return $this->listeners;
    }
    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function getServiceListeners($eventName = '')
    {
        if (!is_string($eventName)) {
            $mess
                =
                'Event name MUST be a string, but given ' . gettype($eventName);
            throw new InvalidArgumentException($mess);
        }
        $this->sortServiceListeners($eventName);
        if ('' !== $eventName) {
            return (!empty($this->serviceListeners[$eventName]))
                ? $this->serviceListeners[$eventName] : [];
        }
        return $this->serviceListeners;
    }
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function removeServiceListener($eventName, array $listener)
    {
        $this->checkEventName($eventName);
        if (!array_key_exists($eventName, $this->serviceListeners)) {
            return $this;
        }
        $this->checkAllowedServiceListener($listener);
        if (in_array($eventName, $this->loadedServices, true)) {
            list($class, $method) = $listener;
            $class = $this->getServiceByName($class);
            $this->removeListener($eventName, [$class, $method]);
        }
        foreach ($this->serviceListeners[$eventName] as $priority => $listeners) {
            $key = array_search($listener, $listeners, true);
            if (false !== $key) {
                unset($this->serviceListeners[$eventName][$priority][$key]);
                // Remove empty priorities.
                if (0 === count(
                    $this->serviceListeners[$eventName][$priority]
                )
                ) {
                    unset($this->serviceListeners[$eventName][$priority]);
                }
                // Remove empty events.
                if (0 === count($this->serviceListeners[$eventName])) {
                    unset($this->serviceListeners[$eventName]);
                }
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function removeServiceSubscriber(
        $serviceName,
        SubscriberInterface $sub
    ) {
        /**
         * @type string|array $listeners
         */
        foreach ($sub->getSubscribedEvents() as $eventName => $listeners) {
            if (is_string($listeners)) {
                $this->removeServiceListener(
                    $eventName,
                    [$serviceName, $listeners]
                );
                continue;
            }
            if (is_string($listeners[0])) {
                $this->removeServiceListener(
                    $eventName,
                    [$serviceName, $listeners[0]],
                    array_key_exists(1, $listeners) ? $listeners[1] : 0
                );
            } elseif (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $this->removeServiceListener(
                        $eventName,
                        [$serviceName, $listener[0]],
                        array_key_exists(1, $listener) ? $listener[1] : 0
                    );
                }
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function trigger($eventName, EventInterface $event = null)
    {
        $this->checkEventName($eventName);
        if (null === $event) {
            $event = new Event();
        }
        $priorities = $this->getListeners($eventName);
        if (0 !== count($priorities)) {
            /**
             * @type array $listeners
             */
            foreach ($priorities as $listeners) {
                foreach ($listeners as $listener) {
                    call_user_func($listener, $event, $eventName, $this);
                    if ($event->hasBeenHandled()) {
                        break;
                    }
                }
            }
        }
        return $event;
    }
    /**
     * This method is used any time the mediator need to get the actual instance
     * of the class for an event.
     *
     * Normal will only be called during actual trigger of an event since lazy
     * loading is used.
     *
     * @param string $serviceName
     *
     * @return callable
     */
    abstract protected function getServiceByName($serviceName);
    /**
     * @param $listener
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    protected function checkAllowedServiceListener($listener)
    {
        if (is_array($listener) && 2 === count($listener)) {
            list($class, $method) = $listener;
            if (!is_string($method)) {
                $mess
                    =
                    'Service listener method name MUST be a string, but given '
                    . gettype($method);
                throw new InvalidArgumentException($mess);
            }
            if ('' === $method) {
                $mess = 'Listener method can NOT be empty';
                throw new DomainException($mess);
            }
            if (is_string($class)) {
                if ('' === $class) {
                    $mess = 'Service listener class name can NOT be empty';
                    throw new DomainException($mess);
                }
                return;
            }
        }
        $mess = 'Service listener MUST be ["className", "methodName"]';
        throw new InvalidArgumentException($mess);
    }
    /**
     * @inheritdoc
     */
    protected function getActualPriority($eventName, $priority)
    {
        if ($priority === 'first') {
            $listenerM = array_key_exists($eventName, $this->listeners) ?
                max(array_keys($this->listeners[$eventName])) + 1 : 1;
            $serviceM = array_key_exists($eventName, $this->serviceListeners) ?
                max(array_keys($this->serviceListeners[$eventName])) + 1 : 1;
            return ($listenerM > $serviceM) ? $listenerM : $serviceM;
        } elseif ($priority === 'last') {
            $listenerM = array_key_exists($eventName, $this->listeners) ?
                min(array_keys($this->listeners[$eventName])) - 1 : -1;
            $serviceM = array_key_exists($eventName, $this->serviceListeners) ?
                min(array_keys($this->serviceListeners[$eventName])) - 1 : -1;
            return ($listenerM < $serviceM) ? $listenerM : $serviceM;
        }
        return (int)$priority;
    }
    /**
     * Used to get the service container.
     *
     * @return Container
     * @throws LogicException
     */
    protected function getServiceContainer()
    {
        if (null === $this->serviceContainer) {
            $mess = 'Tried to access service container before it was set';
            throw new LogicException($mess);
        }
        return $this->serviceContainer;
    }
    /**
     * @param string $eventName
     *
     * @return $this Fluent interface
     */
    protected function lazyLoadServices($eventName = '')
    {
        if (0 === count($this->serviceListeners)) {
            return $this;
        }
        if ('' !== $eventName) {
            if (!array_key_exists($eventName, $this->serviceListeners)) {
                return $this;
            }
            $eventNames = [$eventName];
        } else {
            $eventNames = array_keys($this->serviceListeners);
        }
        foreach ($eventNames as $eventName) {
            if (!in_array($eventName, $this->loadedServices, true)) {
                $this->loadedServices[] = $eventName;
            }
            foreach ($this->serviceListeners[$eventName] as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    list($class, $method) = $listener;
                    $class = $this->getServiceByName($class);
                    $this->addListener(
                        $eventName,
                        [$class, $method],
                        $priority
                    );
                }
            }
        }
        return $this;
    }
    /**
     * @param string $eventName
     *
     * @return $this Fluent Interface
     */
    protected function sortServiceListeners($eventName)
    {
        if (0 === count($this->serviceListeners)) {
            return $this;
        }
        if ('' !== $eventName) {
            if (!array_key_exists($eventName, $this->serviceListeners)) {
                return $this;
            }
            $eventNames = [$eventName];
        } else {
            ksort($this->serviceListeners);
            $eventNames = array_keys($this->listeners);
        }
        foreach ($eventNames as $eventName) {
            ksort($this->serviceListeners[$eventName], SORT_NUMERIC);
        }
        return $this;
    }
    /**
     * @type array $triggeredServices
     */
    protected $loadedServices = [];
    /**
     * Holds the container instance to be used.
     *
     * @type mixed $serviceContainer
     */
    protected $serviceContainer;
    /**
     * Holds the list of service listeners that will be lazy loaded when events
     * are triggered.
     *
     * @type array $serviceListeners
     */
    protected $serviceListeners = [];
}
