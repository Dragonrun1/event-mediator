<?php
/**
 * Contains MockSubscriber class.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\EventMediator;

use EventMediator\EventInterface;
use EventMediator\SubscriberInterface;

/**
 * Class MockSubscriber
 */
class MockSubscriber implements SubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and
     *  respective priorities, or defaults to 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority),
     *  array('methodName2'))
     *
     * @return array The event names to listen for
     */
    public function getSubscribedEvents()
    {
        return $this->subscribedEvents;
    }
    /**
     * @param EventInterface $event
     */
    public function method1(EventInterface $event)
    {
        // Mock event handler
    }
    /**
     * @param array $value
     */
    public function setSubscribedEvents(array $value)
    {
        $this->subscribedEvents = $value;
    }
    /**
     * @type array $subscribedEvents
     */
    protected $subscribedEvents;
}
