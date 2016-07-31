<?php
declare(strict_types=1);
/**
 * Contains class MockServiceSubscriber.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * which has minimal dependencies so it is easy to drop in and use.
 * Copyright (C) 2016 Michael Cummings
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
 * @copyright 2016 Michael Cummings
 * @license   GPL-2.0
 */
namespace Spec\EventMediator;

use EventMediator\ServiceSubscriberInterface;

/**
 * Class MockServiceSubscriber.
 */
class MockServiceSubscriber implements ServiceSubscriberInterface
{
    /**
     * Returns an array of event names this service subscriber wants to listen to.
     *
     * NOTE: The old legacy array format which was trying to emulate one found
     * in Symfony has been dropped and replace with the much cleaner array
     * format that is used internally.
     *
     * The array keys are event names and the value is a list of priorities
     * with each of them containing a list of [containerID, method] for that
     * priority/event pair being subscribed.
     *
     * Pseudo code example:
     *
     * <pre>
     * [
     *     'string eventName' => [
     *         'int|string priority' => [
     *             [
     *                 'string containerID',
     *                 'string methodName'
     *             ], ...
     *         ], ...
     *     ], ...
     * ]
     * </pre>
     *
     * Priority is either an integer, 'first', or 'last' just as seen in addServiceListener().
     *
     * Another example:
     *
     * <pre>
     * [
     *     'event1' => [
     *         100 => [
     *             [
     *                 'My.ContainerID.Event1Listener',
     *                 'listenerMethod'
     *             ]
     *         ]
     *     ],
     *     'event2' => [
     *         'last' => [
     *             [
     *                 'My.ContainerID.Event2Listener',
     *                 'anotherListenerMethod'
     *             ]
     *         ]
     *     ]
     * ]
     * </pre>
     *
     * @return array The event names to listen for
     */
    public function getServiceSubscribedEvents(): array
    {
        // Service dummy.
    }
}
