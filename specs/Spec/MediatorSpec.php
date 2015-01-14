<?php
/**
 * Contains PhpSpec MediatorSpec class.
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

use DomainException;
use EventMediator\Event;
use EventMediator\EventInterface;
use EventMediator\Mediator;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class MediatorSpec
 *
 * @mixin Mediator
 *
 * @method $this shouldHaveCount()
 * @method $this shouldHaveKey()
 * @method void shouldReturn()
 * @method void shouldReturnAnInstanceOf()
 * @method $this shouldThrow()
 * @method void duringAddListener()
 * @method void duringTrigger()
 * @method $this getListeners()
 * @method $this trigger()
 */
class MediatorSpec extends
    ObjectBehavior
{
    /**
     *
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType('EventMediator\Mediator');
    }
    /**
     *
     */
    public function it_returns_fluent_interface_from_addListener()
    {
        $this->addListener('test', [$this, 'test_dummy1'])
             ->shouldReturn($this);
    }
    /**
     *
     */
    public function it_should_always_returns_an_event_from_trigger_even_if_not_given_one(
    )
    {
        /**
         * @type EventInterface $event
         */
        $this->trigger('test', null)
             ->shouldReturnAnInstanceOf('EventMediator\EventInterface');
    }
    public function it_should_ignore_duplicate_listeners_for_the_same_event_and_priority_in_AddListeners(
    )
    {
        $this->addListener('event', [$this, 'test_dummy1']);
        $this->addListener('event', [$this, 'test_dummy1']);
        $this->getListeners('event')
             ->shouldHaveCount(1);
    }
    public function it_should_return_all_listeners_if_event_name_empty_in_getListeners(
    )
    {
        $listeners = [
            ['event1', $this, 'test_dummy1', 'first'],
            ['event2', $this, 'test_dummy1', 0],
            ['event2', $this, 'test_dummy1', 'first']
        ];
        foreach ($listeners as $listener) {
            list($event, $object, $method, $priority) = $listener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners()
             ->shouldHaveCount(2);
        $this->getListeners()
             ->shouldHaveKey('event1');
        $this->getListeners()
             ->shouldHaveKey('event2');
    }
    /**
     *
     */
    public function it_should_return_empty_array_from_getListeners_when_has_no_listeners_for_that_event_name(
    )
    {
        $this->addListener('test2', [$this, 'test_dummy1'])
             ->getListeners('test1')
             ->shouldHaveCount(0);
    }
    /**
     *
     */
    public function it_should_return_empty_array_from_getListeners_when_has_no_listeners_set(
    )
    {
        $this->getListeners()
             ->shouldHaveCount(0);
    }
    public function it_should_return_only_listeners_for_requested_event_name_from_getListeners(
    )
    {
        $listeners = [
            ['event1', $this, 'test_dummy1', 'first'],
            ['event2', $this, 'test_dummy1', 0],
            ['event2', $this, 'test_dummy1', 'last']
        ];
        foreach ($listeners as $listener) {
            list($event, $object, $method, $priority) = $listener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners('event1')
             ->shouldHaveCount(1);
        $this->getListeners('event1')
             ->shouldHaveKey(1);
        $this->getListeners('event2')
             ->shouldHaveCount(2);
        $this->getListeners('event2')
             ->shouldHaveKey(0);
        $this->getListeners('event2')
             ->shouldHaveKey(-1);
    }
    /**
     *
     */
    public function it_should_return_same_event_given_to_trigger_if_it_has_no_listeners(
    )
    {
        $event = new Event();
        $this->trigger('test', $event)
             ->shouldReturn($event);
    }
    /**
     *
     */
    public function it_should_throw_domain_exception_for_empty_event_name_in_addListener(
    )
    {
        $callable = function () {
        };
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddListener('', $callable);
    }
    /**
     *
     */
    public function it_should_throw_domain_exception_for_empty_event_name_on_trigger(
    )
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringTrigger('');
    }
    /**
     *
     */
    public function it_should_throw_invalid_argument_exception_for_non_string_event_name_given_to_addListener(
    )
    {
        $callable = function () {
        };
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $eventName) {
            $mess = 'Event name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringAddListener($eventName, $callable);
        }
    }
    /**
     *
     */
    public function it_should_throw_invalid_argument_exception_for_non_string_event_name_given_to_trigger(
    )
    {
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $eventName) {
            $mess = 'Event name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringTrigger($eventName);
        }
    }
    public function test_dummy1()
    {
        // Just needed for testing.
    }
    public function test_dummy2()
    {
        // Just needed for testing.
    }
}
