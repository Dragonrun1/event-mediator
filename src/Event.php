<?php
declare(strict_types = 1);
/**
 * Contains Event class.
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
 * Class Event
 */
class Event implements EventInterface
{
    /**
     * Listener uses this to let mediator know the event has been handled.
     *
     * @return EventInterface Fluent interface.
     */
    public function eventHandled(): EventInterface
    {
        $this->handled = true;
        return $this;
    }
    /**
     * Used to check if event has already been handled.
     *
     * @return bool Returns true if listener claims to have handled event.
     */
    public function hasBeenHandled(): bool
    {
        return $this->handled;
    }
    /**
     * Used to hold handled status.
     *
     * @var bool
     */
    private $handled = false;
}
