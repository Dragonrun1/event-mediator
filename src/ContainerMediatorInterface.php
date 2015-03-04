<?php
/**
 * ContainerMediatorInterface.php
 *
 * PHP version 5.4
 *
 * @since  20150202 20:32
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace EventMediator;

/**
 * Class AbstractContainerMediator
 */
interface ContainerMediatorInterface extends MediatorInterface
{
    /**
     * Add a service as an event listener.
     *
     * @param string     $eventName Name of the event the listener is being added for.
     * @param array      $listener  Listener to be added.
     * @param int|string $priority  Priority level for the added listener.
     *
     * @return $this Fluent interface.
     */
    public function addServiceListener(
        $eventName,
        array $listener,
        $priority = 0
    );
    /**
     * Add a service as a subscriber to event(s).
     *
     * @param string              $serviceName Name of the event the subscriber is being added for.
     * @param SubscriberInterface $sub         Subscriber to be added.
     *
     * @return $this Fluent interface.
     */
    public function addServiceSubscriber(
        $serviceName,
        SubscriberInterface $sub
    );
    /**
     * Adds service as an subscriber to event(s) using a list of like found in SubscriberInterface.
     *
     * @param string $serviceName Name of the event the subscriber is being added for.
     * @param array  $eventList   List of events the subscriber wishes to be added for. This uses the same format as
     *                            SubscriberInterface.
     *
     * @return $this Fluent interface.
     */
    public function addServiceSubscriberByEventList(
        $serviceName,
        array $eventList
    );
    /**
     * Get a list of service listeners for an event.
     *
     * Note that if event name is empty all listeners will be returned. Any event subscribers are also included in the
     * list.
     *
     * @param string $eventName Name of the event the list of service listeners is needed for.
     *
     * @return array List of event service listeners or empty array if event is unknown or has no listeners or
     *               subscribers.
     */
    public function getServiceListeners($eventName = '');
    /**
     * Remove a service as an event listener.
     *
     * @param string $eventName Event name that listener is being removed from.
     * @param array  $listener  Service listener to be removed.
     *
     * @return $this Fluent interface.
     */
    public function removeServiceListener($eventName, array $listener);
    /**
     * Remove a service subscriber from event(s).
     *
     * @param string              $serviceName Event name that subscriber is being removed from.
     * @param SubscriberInterface $sub         Subscriber to be removed.
     *
     * @return $this Fluent interface.
     */
    public function removeServiceSubscriber(
        $serviceName,
        SubscriberInterface $sub
    );
    /**
     * Removes service as an subscriber to event(s) using a list of like found in SubscriberInterface.
     *
     * @param string $serviceName Event name that subscriber is being removed from.
     * @param array  $eventList   List of events the subscriber wishes to be removed from. This uses the same format as
     *                            SubscriberInterface.
     *
     * @return $this Fluent interface.
     */
    public function removeServiceSubscriberByEventList(
        $serviceName,
        array  $eventList
    );
    /**
     * This is used to bring in the service container that will be used.
     *
     * Though not required it would be considered best practice for this method
     * to create a new instance of the container when given null. Another good
     * practice is to call this method from the class constructor to allow
     * easier testing. For examples of both have a look at
     * PimpleContainerMediator.
     *
     * @see PimpleContainerMediator Container mediator implemented using Pimple.
     *
     * @param mixed $value The service container to be used.
     *
     * @return $this Fluent interface.
     */
    public function setServiceContainer($value = null);
}
