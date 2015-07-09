<?php
/**
 * Contains MockListenerInterface Interface.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec;

use EventMediator\EventInterface;
use EventMediator\MediatorInterface;

/**
 * Interface MockListenerInterface
 */
interface MockListenerInterface
{
    /**
     * @param EventInterface    $event
     * @param string            $eventName
     * @param MediatorInterface $mediator
     *
     * @return EventInterface
     */
    public function method1(
        EventInterface $event,
        $eventName,
        MediatorInterface $mediator
    );
    /**
     * @param EventInterface    $event
     * @param string            $eventName
     * @param MediatorInterface $mediator
     *
     * @return EventInterface
     */
    public function method2(
        EventInterface $event,
        $eventName,
        MediatorInterface $mediator
    );
}
