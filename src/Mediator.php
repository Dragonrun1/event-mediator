<?php
/**
 * Contains Mediator class.
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace EventMediator;

use DomainException;
use InvalidArgumentException;

/**
 * Class Mediator
 */
class Mediator
{
    /**
     * @param string         $eventName
     * @param array|callable $listener
     * @param int|string     $priority
     *
     * @return $this
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->checkEventName($eventName);
        $this->checkAllowedListener($listener);
        if ($priority === 'first') {
            $priority =
                !empty($this->listeners[$eventName]) ?
                    max(array_keys($this->listeners[$eventName])) + 1 : 1;
        } elseif ($priority === 'last') {
            $priority =
                !empty($this->listeners[$eventName]) ?
                    min(array_keys($this->listeners[$eventName])) - 1 : -1;
        }
        if (!empty($this->listeners[$eventName][$priority])) {
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
     * @param string $eventName
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getListeners($eventName = '')
    {
        if (!is_string($eventName)) {
            $mess =
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
     * @param string         $eventName
     * @param EventInterface $event
     *
     * @return EventInterface
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function trigger($eventName, EventInterface $event = null)
    {
        $this->checkEventName($eventName);
        if (null === $event) {
            $event = new Event();
        }
        return $event;
    }
    /**
     * @param $listener
     *
     * @throws InvalidArgumentException
     */
    protected function checkAllowedListener(&$listener)
    {
        if (is_array($listener) && 2 === count($listener)) {
            list($object, $method) = $listener;
            if (!is_string($method)) {
                $mess =
                    'Listener method name MUST be a string, but given '
                    . gettype($method);
                throw new InvalidArgumentException($mess);
            }
            if (is_object($object)) {
                return;
            }
            if (is_callable($object)) {
                $listener[0] = $object;
                return;
            }
            if (is_string($object)) {
                $listener[0] = new $object;
                return;
            }
        }
        $mess =
            'Listener MUST be [object, "methodName"], '
            . '["className", "methodName"], or ' . '[callable, "methodName"]';
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
            $mess =
                'Event name MUST be a string, but given ' . gettype($eventName);
            throw new InvalidArgumentException($mess);
        }
        if ('' === $eventName) {
            $mess = 'Event name can NOT be empty';
            throw new DomainException($mess);
        }
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
            $eventNames = [$eventName];
        } else {
            ksort($this->listeners);
            $eventNames = array_keys($this->listeners);
        }
        foreach ($eventNames as $eventName) {
            if (empty($this->listeners[$eventName])) {
                continue;
            }
            ksort($this->listeners[$eventName], SORT_NUMERIC);
        }
        return $this;
    }
    /**
     * @type array $listeners
     */
    protected $listeners = [];
}
