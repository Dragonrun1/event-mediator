<?php
/**
 * Contains PhpSpec MediatorSpec class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * which has minimal dependencies so it is easy to drop in and use.
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
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class MediatorSpec
 *
 * @mixin \EventMediator\Mediator
 *
 * @method void shouldImplement()
 * @method void shouldHaveListeners()
 * @method void shouldNotHaveListeners()
 * @method void shouldReturn()
 * @method void duringAddListener()
 * @method void duringRemoveListener()
 * @method void duringTrigger()
 * @method $this getListeners()
 * @method $this trigger()
 */
class MediatorSpec extends ObjectBehavior
{
    public function itIsInitializable()
    {
        $this->shouldHaveType('\\EventMediator\\Mediator');
        $this->shouldImplement('\\EventMediator\\MediatorInterface');
    }
    public function itProvidesFluentInterfaceFromAddListener()
    {
        $this->addListener(
            'test',
            ['\Spec\EventMediator\MockListener', 'method1']
        )
             ->shouldReturn($this);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itProvidesFluentInterfaceFromAddSubscriber($sub)
    {
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => ['method1']]);
        $this->addSubscriber($sub)
             ->shouldReturn($this);
    }
    public function itProvidesFluentInterfaceFromRemoveListener()
    {
        $this->removeListener(
            'test',
            ['\\Spec\\EventMediator\\MockListener', 'method1']
        )
             ->shouldReturn($this);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itProvidesFluentInterfaceFromRemoveSubscriber($sub)
    {
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => ['method1']]);
        $this->addSubscriber($sub);
        $this->removeSubscriber($sub)
             ->shouldReturn($this);
    }
    public function itReturnsEmptyArrayBeforeAnyListenersAdded()
    {
        $this->getListeners()
             ->shouldHaveCount(0);
    }
    public function itReturnsEmptyArrayWhenEventHasNoListeners()
    {
        $this->addListener(
            'test2',
            ['\Spec\EventMediator\MockListener', 'method1']
        )
             ->getListeners('test1')
             ->shouldHaveCount(0);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itReturnsMultipleListenerEventsAfterAddingMultipleEventSubscriber(
        $sub
    ) {
        $sub->getSubscribedEvents()
            ->willReturn(
                ['test1' => ['method1', 1], 'test2' => ['method1', 'last']]
            );
        $this->addSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(2);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $this->getListeners()
             ->shouldHaveKey('test2');
    }
    public function itReturnsTrueWhenEventNotGivenButListenersExist()
    {
        $this->shouldNotHaveListeners();
        $listeners = [
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 'first'],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 0],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 'last']
        ];
        foreach ($listeners as $listener) {
            list($event, $object, $method, $priority) = $listener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->shouldHaveListeners();
    }
    /**
     * @param \Spec\EventMediator\MockListener $listener
     * @param \EventMediator\Event             $event
     */
    public function itShouldCallListenersForTheirEventsWhenEventIsTriggered(
        $listener,
        $event
    ) {
        /**
         * @type \EventMediator\MediatorInterface                                   $this
         * @type \Spec\EventMediator\MockListener|\Prophecy\Prophecy\MethodProphecy $listener
         */
        $this->addListener('test1', [$listener, 'method1']);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $this->trigger('test1', $event);
        $listener->method1($event, 'test1', $this)
                 ->shouldHaveBeenCalled();
    }
    public function itShouldGetTheSameEventBackFromTriggerIfThereAreNoListeners(
    )
    {
        $event = new Event();
        $this->trigger('test', $event)
             ->shouldReturn($event);
    }
    public function itShouldHaveLessListenersIfOneIsRemoved()
    {
        $listeners = [
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 0],
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 'first'],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 0]
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
        $this->getListeners('event1')
             ->shouldHaveCount(2);
        $this->removeListener(
            'event1',
            ['\Spec\EventMediator\MockListener', 'method1']
        );
        $this->getListeners('event1')
             ->shouldHaveCount(0);
        $this->getListeners()
             ->shouldHaveCount(1);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itShouldHaveListenerAfterAddingSubscriber($sub)
    {
        $this->getListeners()
             ->shouldHaveCount(0);
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => [['method1'], ['method2']]]);
        $this->addSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(1);
        $this->getListeners()
             ->shouldHaveKey('test1');
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itShouldHaveNoListenersIfOnlySubscriberIsRemoved($sub)
    {
        $sub->getSubscribedEvents()
            ->willReturn(
                [
                    'test1' => ['method1', 1],
                    'test2' => [['method1', 'last'], ['method2']]
                ]
            );
        $this->addSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(2);
        $this->removeSubscriber($sub);
        $this->getListeners()
             ->shouldHaveCount(0);
    }
    public function itShouldIgnoreDuplicateListenersForTheSameEventAndPriority()
    {
        $this->addListener(
            'event',
            ['\Spec\EventMediator\MockListener', 'method1']
        );
        $this->addListener(
            'event',
            ['\Spec\EventMediator\MockListener', 'method1']
        );
        $this->getListeners('event')
             ->shouldHaveCount(1);
    }
    /**
     * @param \Spec\EventMediator\MockListener $listener1
     * @param \Spec\EventMediator\MockListener $listener2
     * @param \EventMediator\Event             $event
     */
    public function itShouldOnlyCallListenersForCurrentEventsWhenEventTriggers(
        $listener1,
        $listener2,
        $event
    ) {
        /**
         * @type \EventMediator\MediatorInterface                                   $this
         * @type \Spec\EventMediator\MockListener|\Prophecy\Prophecy\MethodProphecy $listener1
         * @type \Spec\EventMediator\MockListener|\Prophecy\Prophecy\MethodProphecy $listener2
         */
        $this->addListener('test1', [$listener1, 'method1']);
        $this->addListener('test2', [$listener2, 'method1']);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $this->getListeners()
             ->shouldHaveKey('test2');
        $this->trigger('test2', $event);
        $listener1->method1(
            Argument::type('\EventMediator\EventInterface'),
            Argument::any(),
            Argument::any()
        )
                  ->shouldNotHaveBeenCalled();
        $listener2->method1($event, Argument::is('test2'), $this)
                  ->shouldHaveBeenCalled();
    }
    public function itShouldReturnAllListenersIfEventNameIsEmpty()
    {
        $listeners = [
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 'first'],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 0],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 'first']
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
    public function itShouldReturnOnlyListenersForTheEventRequested()
    {
        $listeners = [
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 'first'],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 0],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 'last']
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
    public function itStillReturnsAnEventFromTriggerEvenIfNoneGiven()
    {
        $this->trigger('test', null)
             ->shouldReturnAnInstanceOf('EventMediator\EventInterface');
    }
    public function itThrowsExceptionForEmptyEventNameWhenAddingListener()
    {
        $callable = function () {
        };
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddListener('', [$callable, 'method1']);
    }
    public function itThrowsExceptionForEmptyEventNameWhenRemovingListener()
    {
        $callable = function () {
        };
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringRemoveListener('', [$callable, 'method1']);
    }
    public function itThrowsExceptionForEmptyEventNameWhenTriggered()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringTrigger('');
    }
    public function itThrowsExceptionForEmptyListenerMethodTypeWhenTryingToAddListener(
    )
    {
        $mess = 'Listener method can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddListener(
                 'test',
                 ['\Spec\EventMediator\MockListener', '']
             );
    }
    public function itThrowsExceptionForInvalidListenerMethodTypesWhenTryingToAddListener(
    )
    {
        $listeners = [
            ['\Spec\EventMediator\MockListener', 123],
            ['\Spec\EventMediator\MockListener', null],
            ['\Spec\EventMediator\MockListener', true]
        ];
        $mess = 'Listener method name MUST be a string, but given ';
        foreach ($listeners as $listener) {
            list($object, $method) = $listener;
            $this->shouldThrow(
                new InvalidArgumentException($mess . gettype($listener[1]))
            )
                 ->duringAddListener('test', [$object, $method]);
        }
    }
    public function itThrowsExceptionForInvalidListenerTypesWhenTryingToAddListener(
    )
    {
        $listeners = [
            [123, 'method1'],
            [true, 'method1'],
            [null, 'method1']
        ];
        $mess = 'Listener MUST be [object, "methodName"],'
                . ' ["className", "methodName"], or'
                . ' [callable, "methodName"]';
        foreach ($listeners as $listener) {
            list($object, $method) = $listener;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringAddListener('test', [$object, $method]);
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToAddListener(
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
                 ->duringAddListener($eventName, [$callable, 'method1']);
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToGetListeners(
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
                 ->duringGetListeners($eventName);
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToRemoveListener(
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
                 ->duringRemoveListener($eventName, [$callable, 'method1']);
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToTrigger()
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
    public function itThrowsExceptionIfFQNListenerDoesNotExistWhenTryingToAddListener(
    )
    {
        $mess
            = 'Listener class \Spec\EventMediator\MockListener1 could NOT be found';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddListener(
                 'test',
                 ['\Spec\EventMediator\MockListener1', 'method1']
             );
    }
    public function itThrowsExceptionIfFQNListenerMethodDoesNotExistWhenTryingToAddListener(
    )
    {
        $mess = sprintf(
            'Listener class %1$s does NOT contain method %2$s',
            '\Spec\EventMediator\MockListener',
            'method2'
        );
        $this->shouldThrow(new InvalidArgumentException($mess))
             ->duringAddListener(
                 'test',
                 ['\Spec\EventMediator\MockListener', 'method2']
             );
    }
    public function itThrowsExceptionIfFQNListenerNameIsEmptyWhenTryingToAddListener(
    )
    {
        $mess = 'Listener class name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddListener('test', ['', 'method1']);
    }
    public function itThrowsExceptionIfObjectListenerMethodDoesNotExistWhenTryingToAddListener(
    )
    {
        $listener = new MockListener();
        $mess = sprintf(
            'Listener class %1$s does NOT contain method %2$s',
            get_class($listener),
            'method2'
        );
        $this->shouldThrow(new InvalidArgumentException($mess))
             ->duringAddListener('test', [$listener, 'method2']);
    }
}
