<?php
/**
 * Contains PhpSpec EventSpec class.
 *
 * PHP version 5.4
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should also be able to find a copy of this license in the included
 * LICENSE file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL-2.0
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\EventMediator;

use PhpSpec\ObjectBehavior;

/**
 * Class EventSpec
 *
 * @mixin \EventMediator\Event
 *
 * @method void shouldImplement($interface)
 * @method void shouldHaveBeenHandled()
 * @method void shouldNotHaveBeenHandled()
 * @method void shouldReturn($result)
 * @method void shouldReturnAnInstanceOf()
 * @method $this shouldThrow()
 * @method void willReturn($result)
 */
class EventSpec extends ObjectBehavior
{
    /**
     *
     */
    public function itIsInitializable()
    {
        $this->shouldHaveType('\\EventMediator\\Event');
        $this->shouldImplement('\\EventMediator\\EventInterface');
    }
    /**
     *
     */
    public function itProvidesFluentInterfaceFromEventHandled()
    {
        $this->eventHandled()
             ->shouldReturn($this);
    }
    /**
     *
     */
    public function itShouldHaveHandledEventAfterEventHandledUsed()
    {
        $this->eventHandled()
             ->shouldHaveBeenHandled();
    }
    /**
     *
     */
    public function itShouldHaveNotHandledEventInitially()
    {
        $this->shouldNotHaveBeenHandled();
    }
}
