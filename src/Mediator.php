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
     * @param string     $eventName
     * @param callable   $listener
     * @param int|string $priority
     *
     * @return $this
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function addListener($eventName, callable $listener, $priority = 0)
    {
        $this->checkEventName($eventName);
        return $this;
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
}
