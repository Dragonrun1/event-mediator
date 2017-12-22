<?php
declare(strict_types = 1);
/**
 * Contains ContainerMediatorInterface Interface.
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
interface ContainerMediatorInterface extends MediatorInterface
{
    /**
     * Add a service as an event listener.
     *
     * @param string     $eventName Name of the event the listener is being added for.
     * @param array      $listener  Listener to be added. ['containerID', 'method']
     * @param int|string $priority  Priority level for the added listener.
     *
     * @return ContainerMediatorInterface Fluent interface.
     */
    public function addServiceListener(string $eventName, array $listener, $priority = 0): ContainerMediatorInterface;
    /**
     * @param array $events
     *
     * @return ContainerMediatorInterface
     */
    public function addServiceListenersByEventList(array $events): ContainerMediatorInterface;
    /**
     * Add a service as a subscriber to event(s).
     *
     * @param ServiceSubscriberInterface $sub         Service subscriber to be added.
     *
     * @return ContainerMediatorInterface Fluent interface.
     */
    public function addServiceSubscriber(ServiceSubscriberInterface $sub): ContainerMediatorInterface;
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
     */
    public function getServiceListeners(string $eventName = ''): array;
    /**
     * Remove a service as an event listener.
     *
     * @param string     $eventName Event name that listener is being removed from.
     * @param array      $listener  Service listener to be removed.
     * @param int|string $priority  Priority level for the to be removed listener.
     *
     * @return ContainerMediatorInterface Fluent interface.
     */
    public function removeServiceListener(
        string $eventName,
        array $listener,
        $priority = 0
    ): ContainerMediatorInterface;
    /**
     * @param array $events
     *
     * @return ContainerMediatorInterface
     */
    public function removeServiceListenersByEventList(array $events): ContainerMediatorInterface;
    /**
     * Remove a service subscriber from event(s).
     *
     * @param ServiceSubscriberInterface $sub Subscriber to be removed.
     *
     * @return ContainerMediatorInterface Fluent interface.
     */
    public function removeServiceSubscriber(ServiceSubscriberInterface $sub): ContainerMediatorInterface;
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
    public function setServiceContainer($value = \null): ContainerMediatorInterface;
}
