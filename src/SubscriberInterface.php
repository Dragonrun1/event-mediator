<?php
declare(strict_types=1);
/**
 * Contains SubscriberInterface Interface.
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
 *
 * Additional licence and copyright information:
 * @copyright 2004-2014 Fabien Potencier <fabien@symfony.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
namespace EventMediator;

/**
 * Interface SubscriberInterface
 *
 * An EventSubscriber knows itself what events it is interested in.
 * If a subscriber is added to a Mediator, the Mediator invokes
 * {@link getSubscribedEvents} and registers the subscriber as a listener for
 * all returned events.
 */
interface SubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * NOTE: The old legacy array format which was trying to emulate one found
     * in Symfony has been dropped and replace with the much cleaner array
     * format that is used internally.
     *
     * The array keys are event names and the value is a list of priorities
     * with each of them containing a list of callables for that
     * priority/event pair.
     *
     * Pseudo code example:
     *
     * <pre>
     * [
     *     'string eventName' => [
     *         'int|string priority' => [
     *             'callable callable', ...
     *         ], ...
     *     ], ...
     * ]
     * </pre>
     *
     * Priority is either an integer, 'first', or 'last' just as seen in addListener().
     *
     * Another example:
     *
     * <pre>
     * [
     *     'event1' => [
     *         100 => [
     *             ['\\MyNS\\event1Listener', 'listenerMethod'],
     *             function (EventInterface $event, string $eventName, MediatorInterface $mediator) {...}
     *         ]
     *     ],
     *     'event2' => [
     *         'last' => [
     *             '\\MyNS\\event2Listener::staticListenerMethod'
     *         ]
     *     ]
     * ]
     * </pre>
     *
     * @return array The event names to listen for
     *
     * @since 2.0.*-dev Changed expected format from the confusing legacy one
     * to a new cleaner one that matches the internal format.
     */
    public function getSubscribedEvents(): array;
}
