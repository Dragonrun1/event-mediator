<?php
/**
 * MediatorInterface.php
 *
 * PHP version 5.4
 *
 * @since  20150130 17:26
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace EventMediator;

/**
 * Class Mediator
 */
interface MediatorInterface
{
    /**
     * @param string         $eventName
     * @param array|callable $listener
     * @param int|string     $priority
     *
     * @return $this Fluent interface
     */
    public function addListener($eventName, $listener, $priority = 0);
    /**
     * @param SubscriberInterface $sub
     *
     * @return $this Fluent interface
     */
    public function addSubscriber(SubscriberInterface $sub);
    /**
     * @param string $eventName
     *
     * @return array
     */
    public function getListeners($eventName = '');
    /**
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners($eventName = '');
    /**
     * @param $eventName
     * @param $listener
     *
     * @return $this Fluent interface
     */
    public function removeListener($eventName, $listener);
    /**
     * @param SubscriberInterface $sub
     *
     * @return $this Fluent interface
     */
    public function removeSubscriber(SubscriberInterface $sub);
    /**
     * @param string         $eventName
     * @param EventInterface $event
     *
     * @return EventInterface
     */
    public function trigger($eventName, EventInterface $event = null);
}
