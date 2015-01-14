<?php
/**
 * Contains PhpSpec EventSpec class.
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
namespace Spec\EventMediator;

use EventMediator\Event;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EventSpec
 *
 * @mixin Event
 *
 * @method void shouldImplement()
 * @method void shouldNotHaveHandled()
 * @method void shouldHaveHandled()
 * @method void shouldReturn()
 * @method void shouldReturnAnInstanceOf()
 * @method $this shouldThrow()
 */
class EventSpec extends
    ObjectBehavior
{
    /**
     *
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType('EventMediator\Event');
        $this->shouldImplement('EventMediator\EventInterface');
    }
    /**
     *
     */
    public function it_returns_fluent_interface_from_eventHandled()
    {
        $this->eventHandled()
             ->shouldReturn($this);
    }
    /**
     *
     */
    public function it_should_have_handled_event_after_eventHandled()
    {
        $this->eventHandled()
             ->shouldHaveHandled();
    }
    /**
     *
     */
    public function it_should_have_not_handled_event_initially()
    {
        $this->shouldNotHaveHandled();
    }
}
