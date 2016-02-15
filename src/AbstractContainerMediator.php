<?php
/**
 * Contains AbstractContainerMediator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * with minimum dependencies so it is easy to drop in and use.
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should also be able to find a copy of this license in the included
 * LICENSE file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL-2.0
 * @author    Michael Cummings
 * <mgcummings@yahoo.com>
 */
namespace EventMediator;

/**
 * Class AbstractContainerMediator
 */
abstract class AbstractContainerMediator extends Mediator implements ContainerMediatorInterface
{
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
     * @return $this Fluent interface.
     */
    abstract public function setServiceContainer($value = null);
    /**
     * Add a service as an event listener.
     *
     * @param string     $eventName Name of the event the listener is being added for.
     * @param array      $listener  Listener to be added.
     * @param int|string $priority  Priority level for the added listener.
     *
     * @return $this Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function addServiceListener($eventName, array $listener, $priority = 0)
    {
        $this->checkEventName($eventName);
        $this->checkAllowedServiceListener($listener);
        $priority = $this->getActualPriority($eventName, $priority);
        if (array_key_exists($eventName, $this->serviceListeners)
            && array_key_exists($priority, $this->serviceListeners[$eventName])
        ) {
            $key = array_search($listener, $this->serviceListeners[$eventName][$priority], true);
            if (false !== $key) {
                return $this;
            }
        }
        $this->serviceListeners[$eventName][$priority][] = $listener;
        return $this;
    }
    /**
     * Add a service as a subscriber to event(s).
     *
     * @param string              $serviceName Name of the event the subscriber is being added for.
     * @param SubscriberInterface $sub         Subscriber to be added.
     *
     * @return $this Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function addServiceSubscriber($serviceName, SubscriberInterface $sub)
    {
        return $this->addServiceSubscriberByEventList($serviceName, $sub->getSubscribedEvents());
    }
    /**
     * Adds service as an subscriber to event(s) using a list of like found in SubscriberInterface.
     *
     * @param string $serviceName Name of the event the subscriber is being added for.
     * @param array  $eventList   List of events the subscriber wishes to be added for. This uses the same format as
     *                            SubscriberInterface.
     *
     * @return $this Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function addServiceSubscriberByEventList($serviceName, array $eventList)
    {
        if (0 === count($eventList)) {
            return $this;
        }
        /**
         * @type string|array $listeners
         */
        foreach ($eventList as $eventName => $listeners) {
            $this->checkEventName($eventName);
            if (is_string($listeners)) {
                $this->addServiceListener($eventName, [$serviceName, $listeners]);
                continue;
            }
            if (is_string($listeners[0])) {
                $this->addServiceListener($eventName, [$serviceName, $listeners[0]],
                    array_key_exists(1, $listeners) ? $listeners[1] : 0);
                continue;
            }
            if (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $this->addServiceListener($eventName, [$serviceName, $listener[0]],
                        array_key_exists(1, $listener) ? $listener[1] : 0);
                }
            }
        }
        return $this;
    }
    /**
     * @param string $eventName
     *
     * @return array
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function getListeners($eventName = '')
    {
        if (!is_string($eventName)) {
            $mess = sprintf('Event name MUST be a string, but given %s', gettype($eventName));
            throw new \InvalidArgumentException($mess);
        }
        $this->lazyLoadServices($eventName);
        return parent::getListeners($eventName);
    }
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
    public function getServiceListeners($eventName = '')
    {
        if (!is_string($eventName)) {
            $mess = sprintf('Event name MUST be a string, but given %s', gettype($eventName));
            throw new \InvalidArgumentException($mess);
        }
        $this->sortServiceListeners($eventName);
        if ('' !== $eventName) {
            return (!empty($this->serviceListeners[$eventName])) ? $this->serviceListeners[$eventName] : [];
        }
        return $this->serviceListeners;
    }
    /**
     * Remove a service as an event listener.
     *
     * @param string $eventName Event name that listener is being removed from.
     * @param array  $listener  Service listener to be removed.
     *
     * @return $this Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
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
                if (0 === count($this->serviceListeners[$eventName][$priority])) {
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
     * Remove a service subscriber from event(s).
     *
     * @param string              $serviceName Event name that subscriber is being removed from.
     * @param SubscriberInterface $sub         Subscriber to be removed.
     *
     * @return $this Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function removeServiceSubscriber($serviceName, SubscriberInterface $sub)
    {
        return $this->removeServiceSubscriberByEventList($serviceName, $sub->getSubscribedEvents());
    }
    /**
     * Removes service as an subscriber to event(s) using a list of like found in SubscriberInterface.
     *
     * @param string $serviceName Event name that subscriber is being removed from.
     * @param array  $eventList   List of events the subscriber wishes to be removed from. This uses the same format as
     *                            SubscriberInterface.
     *
     * @return $this Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function removeServiceSubscriberByEventList($serviceName, array  $eventList)
    {
        /**
         * @type string|array $listeners
         */
        foreach ($eventList as $eventName => $listeners) {
            if (is_string($listeners)) {
                $this->removeServiceListener($eventName, [$serviceName, $listeners]);
                continue;
            }
            if (is_string($listeners[0])) {
                $this->removeServiceListener($eventName, [$serviceName, $listeners[0]]);
            } elseif (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $this->removeServiceListener($eventName, [$serviceName, $listener[0]]);
                }
            }
        }
        return $this;
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
     * @return array
     */
    abstract protected function getServiceByName($serviceName);
    /**
     * @param $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    protected function checkAllowedServiceListener($listener)
    {
        if (is_array($listener) && 2 === count($listener)) {
            list($class, $method) = $listener;
            if (!is_string($method)) {
                $mess = sprintf('Service listener method name MUST be a string, but given %s', gettype($method));
                throw new \InvalidArgumentException($mess);
            }
            if ('' === $method) {
                $mess = 'Listener method can NOT be empty';
                throw new \DomainException($mess);
            }
            if (is_string($class)) {
                if ('' === $class) {
                    $mess = 'Service listener class name can NOT be empty';
                    throw new \DomainException($mess);
                }
                return;
            }
        }
        $mess = 'Service listener MUST be ["className", "methodName"]';
        throw new \InvalidArgumentException($mess);
    }
    /**
     * @param string     $eventName
     * @param string|int $priority
     *
     * @return int
     */
    protected function getActualPriority($eventName, $priority)
    {
        if (is_int($priority)) {
            return $priority;
        }
        $listenerM = parent::getActualPriority($eventName, $priority);
        if ($priority === 'first') {
            $serviceM = array_key_exists($eventName, $this->serviceListeners)
                ? max(array_keys($this->serviceListeners[$eventName])) + 1 : 1;
            $priority = ($listenerM > $serviceM) ? $listenerM : $serviceM;
        } elseif ($priority === 'last') {
            $serviceM = array_key_exists($eventName, $this->serviceListeners)
                ? min(array_keys($this->serviceListeners[$eventName])) - 1 : -1;
            $priority = ($listenerM < $serviceM) ? $listenerM : $serviceM;
        }
        return (int)$priority;
    }
    /**
     * Used to get the service container.
     *
     * @return mixed
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }
    /**
     * @param string $eventName
     *
     * @return $this Fluent interface
     * @throws \DomainException
     * @throws \InvalidArgumentException
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
                    $this->addListener($eventName, [$class, $method], $priority);
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
            krsort($this->serviceListeners[$eventName], SORT_NUMERIC);
        }
        return $this;
    }
    /**
     * List of already loaded services.
     * @type array $loadedServices
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
