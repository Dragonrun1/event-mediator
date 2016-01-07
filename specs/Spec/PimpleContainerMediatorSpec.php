<?php
/**
 * Contains PhpSpec PimpleContainerMediatorSpec class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * with minimal dependencies so it is easy to drop in and use.
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
 * @author    Michael Cummings<mgcummings@yahoo.com>
 */
namespace Spec\EventMediator;

use DomainException;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
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
 * @method $this getListeners()
 * @method $this getServiceListeners()
 * @method $this trigger($value)
 * @method void willReturn($result)
 */
class PimpleContainerMediatorSpec extends ObjectBehavior
{
    public function itIsInitializable()
    {
        $this->shouldHaveType('\\EventMediator\\PimpleContainerMediator');
        $this->shouldImplement('\\EventMediator\\ContainerMediatorInterface');
    }
    public function itProvidesFluentInterfaceFromAddServiceListener()
    {
        $this->addServiceListener('test', ['\DummyClass', 'method1'])
             ->shouldReturn($this);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itProvidesFluentInterfaceFromAddServiceSubscriber($sub)
    {
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => ['method1']]);
        $this->addServiceSubscriber('\DummyClass', $sub)
             ->shouldReturn($this);
    }
    public function itProvidesFluentInterfaceFromAddServiceSubscriberByEventList()
    {
        $this->addServiceSubscriberByEventList(
            '\DummyClass',
            ['test1' => ['method1']]
        )
             ->shouldReturn($this);
    }
    public function itProvidesFluentInterfaceFromRemoveServiceListener()
    {
        $this->removeServiceListener('test', ['\DummyClass', 'method1'])
             ->shouldReturn($this);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itProvidesFluentInterfaceFromRemoveServiceSubscriber($sub)
    {
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => ['method1']]);
        $this->addServiceSubscriber('\DummyClass', $sub);
        $this->removeServiceSubscriber('\DummyClass', $sub)
             ->shouldReturn($this);
    }
    public function itProvidesFluentInterfaceFromRemoveServiceSubscriberByEventList()
    {
        $this->removeServiceSubscriberByEventList(
            '\DummyClass',
            ['test1' => ['method1']]
        )
             ->shouldReturn($this);
    }
    public function itReturnsEmptyArrayBeforeAnyServiceListenersAdded()
    {
        $this->getServiceListeners()
             ->shouldHaveCount(0);
    }
    public function itReturnsEmptyArrayWhenEventHasNoServiceListeners()
    {
        $this->addServiceListener(
            'test2',
            ['\Spec\EventMediator\MockListener', 'method1']
        )
             ->getServiceListeners('test1')
             ->shouldHaveCount(0);
    }
    /**
     * @param \Spec\EventMediator\MockListener $listener
     * @param \EventMediator\Event             $event
     * @param \Pimple\Container                $container
     *
     * @throws InvalidArgumentException
     */
    public function itShouldCallServiceListenersForTheirEventsWhenEventIsTriggered(
        $listener,
        $event,
        $container
    ) {
        /**
         * @type PimpleContainerMediatorSpec|\EventMediator\ContainerMediatorInterface $this
         * @type \Spec\EventMediator\MockListener|\Prophecy\Prophecy\MethodProphecy    $listener
         */
        $this->addServiceListener('test1', ['TestService', 'method1']);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $container->offsetGet('TestService')
                  ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->trigger('test1', $event);
    }
    /**
     * @param \Spec\EventMediator\MockListener   $listener
     * @param \EventMediator\Event               $event
     * @param \Pimple\Container                  $container
     * @param \Spec\EventMediator\MockSubscriber $sub
     *
     * @throws InvalidArgumentException
     */
    public function itShouldCallServiceSubscribersForTheirEventsWhenEventIsTriggered(
        $listener,
        $event,
        $container,
        $sub
    ) {
        /**
         * @type PimpleContainerMediatorSpec|\EventMediator\ContainerMediatorInterface $this
         * @type \Spec\EventMediator\MockListener|\Prophecy\Prophecy\MethodProphecy    $listener
         */
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => ['method1']]);
        $this->addServiceSubscriber('\DummyClass', $sub);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $container->offsetGet('\DummyClass')
                  ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->trigger('test1', $event);
    }
    public function itShouldHaveLessServiceListenersIfOneIsRemoved()
    {
        $listeners = [
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 0],
            ['event1', '\Spec\EventMediator\MockListener', 'method1', 'first'],
            ['event2', '\Spec\EventMediator\MockListener', 'method1', 0]
        ];
        foreach ($listeners as $listener) {
            list($event, $class, $method, $priority) = $listener;
            $this->addServiceListener($event, [$class, $method], $priority);
        }
        $this->getServiceListeners()
             ->shouldHaveCount(2);
        $this->getServiceListeners()
             ->shouldHaveKey('event1');
        $this->getServiceListeners()
             ->shouldHaveKey('event2');
        $this->getServiceListeners('event1')
             ->shouldHaveCount(2);
        $this->removeServiceListener(
            'event1',
            ['\Spec\EventMediator\MockListener', 'method1']
        );
        $this->getServiceListeners('event1')
             ->shouldHaveCount(0);
        $this->getServiceListeners()
             ->shouldHaveCount(1);
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itShouldHaveListenerAfterAddingServiceSubscriber($sub)
    {
        $events = [
            'test1' => [['method1'], ['method2']],
            'test2' => [['method1'], ['method2']]
        ];
        $this->getServiceListeners()
             ->shouldHaveCount(0);
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber('\DummyClass', $sub);
        $this->getServiceListeners()
             ->shouldHaveCount(2);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $this->getServiceListeners()
             ->shouldHaveKey('test2');
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itShouldHaveNoServiceListenersIfOnlyServiceSubscriberIsRemoved(
        $sub
    ) {
        $events = [
            'test1' => [['method1'], ['method2']],
            'test2' => [['method1', 'last'], ['method2']],
            'test3' => 'method1'
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber('\DummyClass', $sub);
        $this->getServiceListeners()
             ->shouldHaveCount(3);
        $this->removeServiceSubscriber('\DummyClass', $sub);
        $this->getServiceListeners()
             ->shouldHaveCount(0);
    }
    public function itShouldIgnoreDuplicateServiceListenersForTheSameEventAndPriority()
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
    public function itShouldIgnoreEmptyEventListWhenAddingServiceSubscriberByEvents()
    {
        $events = [];
        $this->getServiceListeners()
             ->shouldHaveCount(0);
        $this->addServiceSubscriberByEventList('\DummyClass', $events);
        $this->getServiceListeners()
             ->shouldHaveCount(0);
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
        $this->addListener('test2', [$listener2, 'method2']);
        $this->getListeners()
             ->shouldHaveKey('test1');
        $this->getListeners()
             ->shouldHaveKey('test2');
        $event->hasBeenHandled()
              ->willReturn(true);
        $listener2->method2($event, Argument::is('test2'), $this)
                  ->shouldBeCalled();
        $this->trigger('test2', $event);
        $listener1->method1(Argument::type('\EventMediator\EventInterface'), Argument::any(), Argument::any())
                  ->shouldNotHaveBeenCalled();
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
    /**
     * @param \Spec\EventMediator\MockListener   $listener
     * @param \EventMediator\Event               $event
     * @param \Pimple\Container                  $container
     * @param \Spec\EventMediator\MockSubscriber $sub
     *
     * @throws InvalidArgumentException
     */
    public function itShouldStillAllowServiceSubscriberToBeRemovedAfterEventHasBeenTriggered(
        $listener,
        $event,
        $container,
        $sub
    ) {
        /**
         * @type PimpleContainerMediatorSpec|\EventMediator\ContainerMediatorInterface $this
         * @type \Spec\EventMediator\MockListener|\Prophecy\Prophecy\MethodProphecy    $listener
         */
        $sub->getSubscribedEvents()
            ->willReturn(['test1' => ['method1']]);
        $this->addServiceSubscriber('\DummyClass', $sub);
        $this->getServiceListeners()
             ->shouldHaveKey('test1');
        $container->offsetGet('\DummyClass')
                  ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
                 ->shouldBeCalled();
        $this->trigger('test1', $event);
        $this->removeServiceSubscriber('\DummyClass', $sub);
        $this->getServiceListeners()
             ->shouldNotHaveKey('test1');
    }
    public function itThrowsExceptionForEmptyEventNameWhenAddingServiceListener()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddServiceListener('', ['\DummyClass', 'method1']);
    }
    public function itThrowsExceptionForEmptyEventNameWhenRemovingServiceListener()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringRemoveServiceListener('', ['\DummyClass', 'method1']);
    }
    public function itThrowsExceptionForEmptyListenerClassNameWhenTryingToAddServiceListener()
    {
        $mess = 'Service listener class name can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddServiceListener(
                 'test',
                 ['', 'test1']
             );
    }
    public function itThrowsExceptionForEmptyListenerMethodNameWhenTryingToAddServiceListener()
    {
        $mess = 'Listener method can NOT be empty';
        $this->shouldThrow(new DomainException($mess))
             ->duringAddServiceListener(
                 'test',
                 ['\DummyClass', '']
             );
    }
    /**
     * @param \Spec\EventMediator\MockSubscriber $sub
     */
    public function itThrowsExceptionForIncorrectServiceContainerTypeWhenTryingToSetContainer(
        $sub
    ) {
        $mess = sprintf(
            'Must be an instance of Pimple Container but given %s',
            gettype($sub)
        );
        $this->shouldThrow(new InvalidArgumentException($mess))
             ->duringSetServiceContainer($sub);
    }
    public function itThrowsExceptionForInvalidListenerTypesWhenTryingToAddServiceListener()
    {
        $listeners = [
            [123, 'method1'],
            [true, 'method1'],
            [null, 'method1']
        ];
        $mess = 'Service listener MUST be ["className", "methodName"]';
        foreach ($listeners as $listener) {
            list($class, $method) = $listener;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringAddServiceListener('test', [$class, $method]);
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToAddServiceListener()
    {
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $eventName) {
            $mess = 'Event name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringAddServiceListener(
                     $eventName,
                     ['\DummyClass', 'method1']
                 );
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToGetListeners()
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
    public function itThrowsExceptionForNonStringEventNameWhenTryingToGetServiceListeners()
    {
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $eventName) {
            $mess = 'Event name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringGetServiceListeners($eventName);
        }
    }
    public function itThrowsExceptionForNonStringEventNameWhenTryingToRemoveServiceListener()
    {
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $eventName) {
            $mess = 'Event name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringRemoveServiceListener(
                     $eventName,
                     ['\DummyClass', 'method1']
                 );
        }
    }
    public function itThrowsExceptionForNonStringListenerMethodNameWhenTryingToAddServiceListener()
    {
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $methodName) {
            $mess = 'Service listener method name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringAddServiceListener(
                     'test',
                     ['\DummyClass', $methodName]
                 );
        }
    }
    public function itThrowsExceptionForNonStringListenerMethodNameWhenTryingToRemoveServiceListener()
    {
        $this->addServiceListener('test', ['\DummyClass', 'method1']);
        $messages = [
            'array'   => [],
            'integer' => 0,
            'NULL'    => null
        ];
        foreach ($messages as $mess => $methodName) {
            $mess = 'Service listener method name MUST be a string, but given ' . $mess;
            $this->shouldThrow(new InvalidArgumentException($mess))
                 ->duringRemoveServiceListener(
                     'test',
                     ['\DummyClass', $methodName]
                 );
        }
    }
}
