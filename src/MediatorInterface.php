<?php
declare(strict_types = 1);
/**
 * MediatorInterface.php
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
interface MediatorInterface
{
    /**
     * @param string     $eventName
     * @param callable   $listener
     * @param int|string $priority
     *
     * @return MediatorInterface Fluent interface
     */
    public function addListener(string $eventName, callable $listener, $priority = 0): MediatorInterface;
    /**
     * @param array $events
     *
     * @return MediatorInterface Fluent interface.
     */
    public function addListenersByEventList(array $events): MediatorInterface;
    /**
     * @param SubscriberInterface $sub
     *
     * @return MediatorInterface Fluent interface
     */
    public function addSubscriber(SubscriberInterface $sub): MediatorInterface;
    /**
     * @param string $eventName
     *
     * @return array
     */
    public function getListeners(string $eventName = ''): array;
    /**
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners(string $eventName = ''): bool;
    /**
     * @param string     $eventName
     * @param callable   $listener
     * @param int|string $priority
     *
     * @return MediatorInterface Fluent interface
     */
    public function removeListener(string $eventName, callable $listener, $priority = 0): MediatorInterface;
    /**
     * @param array $events Events to be removed.
     *
     * @return MediatorInterface Fluent interface.
     */
    public function removeListenersByEventList(array $events): MediatorInterface;
    /**
     * @param SubscriberInterface $sub
     *
     * @return MediatorInterface Fluent interface
     */
    public function removeSubscriber(SubscriberInterface $sub): MediatorInterface;
    /**
     * @param string              $eventName
     * @param EventInterface|null $event
     *
     * @return EventInterface
     */
    public function trigger(string $eventName, EventInterface $event = \null): EventInterface;
}
