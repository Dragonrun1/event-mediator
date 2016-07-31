<?php
declare(strict_types = 1);
/**
 * Contains PhpSpec MediatorSpec class.
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
namespace Spec\EventMediator;

use EventMediator\Event;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class MediatorSpec
 *
 * @mixin \EventMediator\Mediator
 *
 * @method void shouldImplement($interface)
 * @method void shouldHaveListeners()
 * @method void shouldNotHaveListeners()
 * @method void shouldReturn($result)
 * @method void willReturn($result)
 */
class MediatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\\EventMediator\\Mediator');
        $this->shouldImplement('\\EventMediator\\MediatorInterface');
    }
    public function it_provides_fluent_interface_from_add_listener(MockListener $listener)
    {
        $this->addListener('test', [$listener, 'method1'])
             ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_add_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub)
             ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_remove_listener(MockListener $listener)
    {
        $this->removeListener('test', [$listener, 'method1'])
             ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_remove_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->removeSubscriber($sub)
             ->shouldReturn($this);
    }
    public function it_returns_empty_array_before_any_listeners_added()
    {
        $this->getListeners()
             ->shouldHaveCount(0);
    }
    public function it_returns_empty_array_when_event_has_no_listeners(MockListener $listener)
    {
        $this->addListener('test2', [$listener, 'method1'])
             ->getListeners('test1')
             ->shouldHaveCount(0);
    }
    public function it_returns_multiple_listener_events_after_adding_multiple_event_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ],
            'test2' => [
                'last' => [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(2);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $this->getListeners()
             ->shouldHaveKey('test2');
    }
    public function it_returns_true_when_event_not_given_but_listeners_exist(MockListener $listener)
    {
        $this->shouldNotHaveListeners();
        $listeners = [
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0],
            ['event2', $listener, 'method1', 'last']
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->shouldHaveListeners();
    }
    /**
     * Issue #1 - Mediator calls listeners in wrong order.
     *
     * @param MockListener $listener
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_call_listeners_for_their_events_in_correct_priority_order_when_event_is_triggered(
        MockListener $listener,
        Event $event
    ) {
        $event->hasBeenHandled()
              ->willReturn(false);
        $this->addListener('test1', [$listener, 'method1']);
        $this->addListener('test1', [$listener, 'method2']);
        $this->addListener('test1', [$listener, 'method1'], 'first');
        $this->addListener('test1', [$listener, 'method1'], 'last');
        $this->getListeners()
             ->shouldHaveKey('test1');
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $listener->method1($event, 'test1', $this)
                 ->willReturn($event);
        $listener->method2($event, 'test1', $this)
                 ->shouldBeCalled();
        $listener->method2($event, 'test1', $this)
                 ->willReturn($event);
        $this->trigger('test1', $event);
        $expected = [
            1 => [[$listener, 'method1']],
            0 => [[$listener, 'method1'], [$listener, 'method2']],
            -1 => [[$listener, 'method1']]
        ];
        $this->getListeners('test1')
             ->shouldReturn($expected);
    }
    /**
     * @param MockListener $listener
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_call_listeners_for_their_events_when_event_is_triggered(
        MockListener $listener,
        Event $event
    ) {
        $event->hasBeenHandled()
              ->willReturn(false);
        $listener->method1($event, 'test1', $this)
                 ->willReturn($event);
        $this->addListener('test1', [$listener, 'method1']);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->trigger('test1', $event);
    }
    public function it_should_get_the_same_event_back_from_trigger_if_there_are_no_listeners()
    {
        $event = new Event();
        $this->trigger('test', $event)
             ->shouldReturn($event);
    }
    public function it_should_have_less_listeners_if_one_is_removed(MockListener $listener)
    {
        $listeners = [
            ['event1', $listener, 'method1', 0],
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0]
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners()
             ->shouldHaveCount(2);
        $this->getListeners()
             ->shouldHaveKey('event1');
        $this->getListeners()
             ->shouldHaveKey('event2');
        $this->getListeners('event1')
             ->shouldHaveCount(2);
        $this->removeListener('event1', [$listener, 'method1']);
        $this->getListeners('event1')
             ->shouldHaveCount(1);
        $this->removeListener('event1', [$listener, 'method1'], 'first');
        $this->getListeners('event1')
             ->shouldHaveCount(0);
        $this->getListeners()
             ->shouldHaveCount(1);
    }
    /**
     * @param MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function it_should_have_listener_after_adding_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ],
                    [
                        $sub,
                        'method2'
                    ]
                ]
            ]
        ];
        $this->getListeners()
             ->shouldHaveCount(0);
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(1);
        $this->getListeners()
             ->shouldHaveKey('test1');
    }
    /**
     * @param MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function it_should_have_no_listeners_if_only_subscriber_is_removed(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                1 => [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ],
            'test2' => [
                'last' => [
                    [
                        $sub,
                        'method1'
                    ]
                ],
                [
                    [
                        $sub,
                        'method2'
                    ]
                ]
            ],
            'test3' => [
                1 => [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(3);
        $this->removeSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(0);
    }
    public function it_should_ignore_duplicate_listeners_for_the_same_event_and_priority(MockListener $listener)
    {
        $this->addListener('event', [$listener, 'method1']);
        $this->addListener('event', [$listener, 'method1']);
        $this->getListeners('event')
             ->shouldHaveCount(1);
    }
    /**
     * @param MockListener $listener1
     * @param MockListener $listener2
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_only_call_listeners_for_current_events_when_event_triggers(
        MockListener $listener1,
        MockListener $listener2,
        Event $event
    ) {
        $this->addListener('test1', [$listener1, 'method1']);
        $this->addListener('test2', [$listener2, 'method2']);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $this->getListeners()
             ->shouldHaveKey('test2');
        $event->hasBeenHandled()
              ->willReturn(true);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $listener2->method2(Argument::type('\EventMediator\EventInterface'), Argument::is('test2'), $this)
                  ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $listener1->method1(Argument::type('\EventMediator\EventInterface'), Argument::any(),
            Argument::type('\EventMediator\MediatorInterface'))
                  ->shouldNotBeCalled();
        $this->trigger('test2', $event);
    }
    /**
     * Issue #2 - Higher priority handles don't stop lower priority listeners from seeing event.
     *
     * @param MockListener $listener
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_only_call_listeners_for_event_until_one_of_them_handles_the_event(
        MockListener $listener,
        Event $event
    ) {
        $event->hasBeenHandled()
              ->willReturn(true)
              ->shouldBeCalled();
        $listener->method2($event, 'test1', $this)
                 ->willReturn($event);
        $this->addListener('test1', [$listener, 'method2']);
        $this->addListener('test1', [$listener, 'method1'], 'last');
        $this->getListeners()
             ->shouldHaveKey('test1');
        $expected = [0 => [[$listener, 'method2']], -1 => [[$listener, 'method1']]];
        $this->getListeners('test1')
             ->shouldReturn($expected);
        $listener->method2($event, 'test1', $this)
                 ->shouldBeCalled();
        $listener->method1($event, 'test1', $this)
                 ->shouldNotBeCalled();
        $this->trigger('test1', $event);
    }
    public function it_should_return_all_listeners_if_event_name_is_empty(MockListener $listener)
    {
        $listeners = [
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0],
            ['event2', $listener, 'method1', 'first']
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners()
             ->shouldHaveCount(2);
        $this->getListeners()
             ->shouldHaveKey('event1');
        $this->getListeners()
             ->shouldHaveKey('event2');
    }
    public function it_should_return_only_listeners_for_the_event_requested(MockListener $listener)
    {
        $listeners = [
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0],
            ['event2', $listener, 'method1', 'last']
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
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
    public function it_still_returns_an_event_from_trigger_even_if_none_given()
    {
        $this->trigger('test', null)
             ->shouldReturnAnInstanceOf('EventMediator\EventInterface');
    }
    public function it_throws_exception_for_empty_event_name_when_adding_listener(MockListener $listener)
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
             ->during('addListener', ['', [$listener, 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_removing_listener(MockListener $listener)
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
             ->during('removeListener', ['', [$listener, 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_triggered()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
             ->during('trigger', ['']);
    }
    public function it_throws_exception_for_invalid_event_name_when_adding_listener(MockListener $listener)
    {
        $mess = 'Using any non-printable characters in the event name is NOT allowed';
        $this->shouldThrow(new \DomainException($mess))
             ->during('addListener', ["\001", [$listener, 'method1']]);
    }
    public function it_throws_exception_for_missing_listeners_when_add_listeners_by_event_list()
    {
        $events = [
            'test1' => [0]
        ];
        $mess = 'Must have at least one listener per listed priority';
        $this->shouldThrow(new \LengthException($mess))
             ->during('addListenersByEventList', [$events]);
        $events = [
            'test1' => [0 => []]
        ];
        $mess = 'Must have at least one listener per listed priority';
        $this->shouldThrow(new \LengthException($mess))
             ->during('addListenersByEventList', [$events]);
    }
    public function it_throws_exception_for_missing_priorities_when_add_listeners_by_event_list()
    {
        $events = [
            'test1'
        ];
        $mess = 'Must have as least one priority per listed event';
        $this->shouldThrow(new \LengthException($mess))
             ->during('addListenersByEventList', [$events]);
        $events = [
            'test1' => []
        ];
        $mess = 'Must have as least one priority per listed event';
        $this->shouldThrow(new \LengthException($mess))
             ->during('addListenersByEventList', [$events]);
    }
}
