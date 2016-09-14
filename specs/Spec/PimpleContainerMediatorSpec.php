<?php
declare(strict_types = 1);
/**
 * Contains PhpSpec PimpleContainerMediatorSpec class.
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
use Pimple\Container;
use Prophecy\Argument;

/**
 * Class PimpleContainerMediatorSpec
 *
 * @mixin \EventMediator\PimpleContainerMediator
 *
 * @method void shouldImplement($interface)
 * @method void shouldHaveListeners()
 * @method void shouldNotHaveListeners()
 * @method void shouldReturn($result)
 * @method void duringAddServiceListener()
 * @method void duringGetServiceListeners($value)
 * @method void duringRemoveServiceListener()
 * @method void duringTrigger()
 * @method void willReturn($result)
 */
class PimpleContainerMediatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\\EventMediator\\PimpleContainerMediator');
        $this->shouldImplement('\\EventMediator\\ContainerMediatorInterface');
    }
    public function it_provides_fluent_interface_from_add_service_listener()
    {
        $this->addServiceListener('test', ['\DummyClass', 'method1'])
             ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_add_service_subscriber(MockServiceSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub)
             ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_remove_service_listener()
    {
        $this->removeServiceListener('test', ['\DummyClass', 'method1'])
             ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_remove_service_subscriber(MockServiceSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->removeServiceSubscriber($sub)
             ->shouldReturn($this);
    }
    public function it_returns_empty_array_before_any_service_listeners_added()
    {
        $this->getServiceListeners()
             ->shouldHaveCount(0);
    }
    public function it_returns_empty_array_when_event_has_no_service_listeners()
    {
        $this->addServiceListener('test2', ['ContainerID1', 'method1'])
             ->getServiceListeners('test1')
             ->shouldHaveCount(0);
    }
    /**
     * @param MockListener $listener
     * @param Event        $event
     * @param Container    $container
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_call_service_listeners_for_their_events_when_event_is_triggered(
        MockListener $listener,
        Event $event,
        Container $container
    ) {
        $event->hasBeenHandled()
              ->willReturn(false);
        $this->addServiceListener('test1', ['ContainerID1', 'method1']);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $container->offsetGet('ContainerID1')
                  ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->trigger('test1', $event);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param MockListener          $listener
     * @param Event                 $event
     * @param Container             $container
     * @param MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \LogicException
     */
    public function it_should_call_service_subscribers_for_their_events_when_event_is_triggered(
        MockListener $listener,
        Event $event,
        Container $container,
        MockServiceSubscriber $sub
    ) {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $event->hasBeenHandled()
              ->willReturn(false);
        $listener->method1($event, 'test1', $this)
                 ->willReturn($event);
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $container->offsetGet('containerID1')
                  ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->getServiceByName('containerID1')
             ->shouldReturn($listener);
        $this->trigger('test1', $event);
    }
    public function it_should_have_less_service_listeners_if_one_is_removed()
    {
        $listeners = [
            ['event1', 'containerID1', 'method1', 0],
            ['event1', 'containerID1', 'method1', 'first'],
            ['event2', 'containerID1', 'method1', 0]
        ];
        foreach ($listeners as $listener) {
            list($event, $containerID, $method, $priority) = $listener;
            $this->addServiceListener($event, [$containerID, $method], $priority);
        }
        $this->getServiceListeners()
             ->shouldHaveCount(2);
        $this->getServiceListeners()
             ->shouldHaveKey('event1');
        $this->getServiceListeners()
             ->shouldHaveKey('event2');
        $this->getServiceListeners('event1')
             ->shouldHaveCount(2);
        $this->removeServiceListener('event1', ['containerID1', 'method1'], 'first');
        $this->getServiceListeners('event1')
             ->shouldHaveCount(1);
        $this->removeServiceListener('event1', ['containerID1', 'method1']);
        $this->getServiceListeners('event1')
             ->shouldHaveCount(0);
        $this->getServiceListeners()
             ->shouldHaveCount(1);
    }
    /**
     * @param MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function it_should_have_listener_after_adding_service_subscriber(MockServiceSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ],
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ],
            'test2' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ],
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ]
        ];
        $this->getServiceListeners()
             ->shouldHaveCount(0);
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
             ->shouldHaveCount(2);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $this->getServiceListeners()
             ->shouldHaveKey('test2');
    }
    /**
     * @param MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function it_should_have_no_service_listeners_if_only_service_subscriber_is_removed(
        MockServiceSubscriber $sub
    ) {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ],
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ],
            'test2' => [
                'last' => [
                    [
                        'containerID1',
                        'method1'
                    ]
                ],
                [
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ],
            'test3' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
             ->shouldHaveCount(3);
        $this->removeServiceSubscriber($sub);
        $this->getServiceListeners()
             ->shouldHaveCount(0);
    }
    public function it_should_ignore_duplicate_service_listeners_for_the_same_event_and_priority()
    {
        $this->addServiceListener(
            'event',
            ['\Spec\EventMediator\MockListener', 'method1']
        );
        $this->addServiceListener(
            'event',
            ['\Spec\EventMediator\MockListener', 'method1']
        );
        $this->getServiceListeners('event')
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
        $listener2->method2($event, Argument::is('test2'), $this)
                  ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $listener1->method1($event, Argument::any(), $this)
                  ->shouldNotHaveBeenCalled();
        $this->trigger('test2', $event);
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
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param MockListener          $listener
     * @param Event                 $event
     * @param Container             $container
     * @param MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \LogicException
     */
    public function it_should_still_allow_service_subscriber_to_be_removed_after_event_has_been_triggered(
        MockListener $listener,
        Event $event,
        Container $container,
        MockServiceSubscriber $sub
    ) {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $event->hasBeenHandled()
              ->willReturn(false);
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $container->offsetGet('containerID1')
                  ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->getServiceByName('containerID1')
             ->shouldReturn($listener);
        $this->trigger('test1', $event);
        $this->removeServiceSubscriber($sub);
        $this->getServiceListeners()
             ->shouldNotHaveKey('test1');
    }
    public function it_throws_exception_for_badly_formed_listener_when_trying_to_add_service_listener()
    {
        $mess = 'Service listener form MUST be ["containerID", "methodName"]';
        $this->shouldThrow(new \InvalidArgumentException($mess))
             ->during('addServiceListener', ['test', ['methodName']]);
    }
    public function it_throws_exception_for_empty_event_name_when_adding_service_listener()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
             ->during('addServiceListener', ['', ['\DummyClass', 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_removing_service_listener()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
             ->during('removeServiceListener', ['', ['\DummyClass', 'method1']]);
    }
    public function it_throws_exception_for_empty_listener_class_name_when_trying_to_add_service_listener()
    {
        $mess = 'Using any non-printable characters in the container ID is NOT allowed';
        $this->shouldThrow(new \InvalidArgumentException($mess))
             ->during('addServiceListener', ['test', ['', 'test1']]);
    }
    public function it_throws_exception_for_empty_listener_method_name_when_trying_to_add_service_listener()
    {
        $mess = 'Service listener method name format is invalid, was given ';
        $this->shouldThrow(new \InvalidArgumentException($mess))
             ->during('addServiceListener', ['test', ['\DummyClass', '']]);
    }
    /**
     * @param MockSubscriber $sub
     */
    public function it_throws_exception_for_incorrect_service_container_type_when_trying_to_set_container(
        MockSubscriber $sub
    ) {
        $mess = sprintf(
            'Must be an instance of Pimple Container but given %s',
            gettype($sub)
        );
        $this->shouldThrow(new \InvalidArgumentException($mess))
             ->during('setServiceContainer', [$sub]);
    }
    public function it_throws_exception_for_invalid_listener_types_when_trying_to_add_service_listener()
    {
        $listeners = [
            [123, 'method1'],
            [true, 'method1'],
            [null, 'method1']
        ];
        $mess = 'Service listener container ID MUST be a string, but was given ';
        foreach ($listeners as $listener) {
            list($class, $method) = $listener;
            $this->shouldThrow(new \InvalidArgumentException($mess . gettype($class)))
                 ->during('addServiceListener', ['test', [$class, $method]]);
        }
    }
    public function it_throws_exception_for_non_string_listener_method_name_when_trying_to_add_service_listener()
    {
        $messages = [
            'array' => [],
            'integer' => 0,
            'NULL' => null
        ];
        foreach ($messages as $mess => $methodName) {
            $mess = 'Service listener method name MUST be a string, but was given ' . $mess;
            $this->shouldThrow(new \InvalidArgumentException($mess))
                 ->during('addServiceListener', ['test', ['\DummyClass', $methodName]]);
        }
    }
    public function it_throws_exception_for_non_string_listener_method_name_when_trying_to_remove_service_listener()
    {
        $this->addServiceListener('test', ['\DummyClass', 'method1']);
        $messages = [
            'array' => [],
            'integer' => 0,
            'NULL' => null
        ];
        foreach ($messages as $mess => $methodName) {
            $mess = 'Service listener method name MUST be a string, but was given ' . $mess;
            $this->shouldThrow(new \InvalidArgumentException($mess))
                 ->during('removeServiceListener', ['test', ['\DummyClass', $methodName]]);
        }
    }
    public function it_throws_exception_for_unknown_priorities_when_trying_to_add_service_listener()
    {
        $mess = 'Unknown priority was given only "first", "last", or integer may be used';
            $this->shouldThrow(new \InvalidArgumentException($mess))
                 ->during('addServiceListener', ['test', ['\DummyClass', 'method1'], 'Yoo!']);
    }
}
