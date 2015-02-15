<?php
/**
 * Contains Mediator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * which has minimal dependencies so it is easy to drop in and use.
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace EventMediator;

use DomainException;
use InvalidArgumentException;

/**
 * Class Mediator
 */
class Mediator implements MediatorInterface
{
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function addListener($eventName, array $listener, $priority = 0)
    {
        $this->checkEventName($eventName);
        $this->checkAllowedListener($listener);
        $priority = $this->getActualPriority($eventName, $priority);
        if (array_key_exists($eventName, $this->listeners)
            && array_key_exists($priority, $this->listeners[$eventName])
        ) {
            $key = array_search(
                $listener,
                $this->listeners[$eventName][$priority],
                true
            );
            if (false !== $key) {
                return $this;
            }
        }
        $this->listeners[$eventName][$priority][] = $listener;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function addSubscriber(SubscriberInterface $sub)
    {
        /**
         * @type string|array $listeners
         */
        foreach ($sub->getSubscribedEvents() as $eventName => $listeners) {
            if (is_string($listeners)) {
                $this->addListener($eventName, [$sub, $listeners]);
                continue;
            }
            if (is_string($listeners[0])) {
                $this->addListener(
                    $eventName,
                    [$sub, $listeners[0]],
                    array_key_exists(1, $listeners) ? $listeners[1] : 0
                );
            } elseif (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $this->addListener(
                        $eventName,
                        [$sub, $listener[0]],
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
            $mess
                =
                'Event name MUST be a string, but given ' . gettype($eventName);
            throw new InvalidArgumentException($mess);
        }
        $this->sortListeners($eventName);
        if ('' !== $eventName) {
            return (!empty($this->listeners[$eventName]))
                ? $this->listeners[$eventName] : [];
        }
        return $this->listeners;
    }
    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function hasListeners($eventName = '')
    {
        return (bool)$this->getListeners($eventName);
    }
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function removeListener($eventName, array $listener)
    {
        $this->checkEventName($eventName);
        if (!array_key_exists($eventName, $this->listeners)) {
            return $this;
        }
        $this->checkAllowedListener($listener);
        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            $key = array_search($listener, $listeners, true);
            if (false !== $key) {
                unset($this->listeners[$eventName][$priority][$key]);
                if (0 === count($this->listeners[$eventName][$priority])) {
                    unset($this->listeners[$eventName][$priority]);
                }
                if (0 === count($this->listeners[$eventName])) {
                    unset($this->listeners[$eventName]);
                }
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function removeSubscriber(SubscriberInterface $sub)
    {
        /**
         * @type string|array $listeners
         */
        foreach ($sub->getSubscribedEvents() as $eventName => $listeners) {
            if (is_string($listeners)) {
                $this->removeListener($eventName, [$sub, $listeners]);
                continue;
            }
            if (is_string($listeners[0])) {
                $this->removeListener(
                    $eventName,
                    [$sub, $listeners[0]],
                    array_key_exists(1, $listeners) ? $listeners[1] : 0
                );
            } elseif (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $this->removeListener(
                        $eventName,
                        [$sub, $listener[0]],
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
     * @param $listener
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    protected function checkAllowedListener($listener)
    {
        if (is_array($listener) && 2 === count($listener)) {
            list($object, $method) = $listener;
            if (!is_string($method)) {
                $mess = 'Listener method name MUST be a string, but given '
                        . gettype($method);
                throw new InvalidArgumentException($mess);
            }
            if ('' === $method) {
                $mess = 'Listener method can NOT be empty';
                throw new DomainException($mess);
            }
            if (is_string($object)) {
                if ('' === $object) {
                    $mess = 'Listener class name can NOT be empty';
                    throw new DomainException($mess);
                }
                if (!class_exists($object)) {
                    $mess = sprintf(
                        'Listener class %s could NOT be found',
                        $object
                    );
                    throw new DomainException($mess);
                }
                if (!in_array($method, get_class_methods($object), true)) {
                    $mess = sprintf(
                        'Listener class %1$s does NOT contain method %2$s',
                        $object,
                        $method
                    );
                    throw new InvalidArgumentException($mess);
                }
                return;
            }
            if (is_object($object)) {
                if (!in_array($method, get_class_methods($object), true)) {
                    $mess = sprintf(
                        'Listener class %1$s does NOT contain method %2$s',
                        get_class($object),
                        $method
                    );
                    throw new InvalidArgumentException($mess);
                }
                return;
            }
            if (is_callable($object)) {
                return;
            }
        }
        $mess = 'Listener MUST be [object, "methodName"], '
                . '["className", "methodName"], or '
                . '[callable, "methodName"]';
        throw new InvalidArgumentException($mess);
    }
    /**
     * @param $eventName
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    protected function checkEventName($eventName)
    {
        if (!is_string($eventName)) {
            $mess
                =
                'Event name MUST be a string, but given ' . gettype($eventName);
            throw new InvalidArgumentException($mess);
        }
        if ('' === $eventName) {
            $mess = 'Event name can NOT be empty';
            throw new DomainException($mess);
        }
    }
    /**
     * @param string     $eventName
     * @param string|int $priority
     *
     * @return int
     */
    protected function getActualPriority($eventName, $priority)
    {
        if ($priority === 'first') {
            $priority = !empty($this->listeners[$eventName]) ?
                max(array_keys($this->listeners[$eventName])) + 1 : 1;
            return $priority;
        } elseif ($priority === 'last') {
            $priority = !empty($this->listeners[$eventName]) ?
                min(array_keys($this->listeners[$eventName])) - 1 : -1;
            return $priority;
        }
        return (int)$priority;
    }
    /**
     * @param string $eventName
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function sortListeners($eventName = '')
    {
        if (0 === count($this->listeners)) {
            return $this;
        }
        if ('' !== $eventName) {
            if (!array_key_exists($eventName, $this->listeners)) {
                return $this;
            }
            $eventNames = [$eventName];
        } else {
            ksort($this->listeners);
            $eventNames = array_keys($this->listeners);
        }
        foreach ($eventNames as $eventName) {
            ksort($this->listeners[$eventName], SORT_NUMERIC);
        }
        return $this;
    }
    /**
     * @type array $listeners
     */
    protected $listeners = [];
}
