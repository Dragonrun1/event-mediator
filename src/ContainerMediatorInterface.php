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
     * @param string     $eventName
     * @param array      $listener
     * @param int|string $priority
     *
     * @return $this
     */
    public function addServiceListener(
        $eventName,
        array $listener,
        $priority = 0
    );
    /**
     * @param string              $serviceName
     * @param SubscriberInterface $sub
     *
     * @return $this
     */
    public function addServiceSubscriber(
        $serviceName,
        SubscriberInterface $sub
    );
    /**
     * @param string $eventName
     *
     * @return array
     */
    public function getServiceListeners($eventName = '');
    /**
     * Add a service as an event listener.
     *
     * @param string $eventName
     * @param array  $listener
     *
     * @return $this
     */
    public function removeServiceListener($eventName, array $listener);
    /**
     * @param string              $serviceName
     * @param SubscriberInterface $sub
     *
     * @return $this
     */
    public function removeServiceSubscriber(
        $serviceName,
        SubscriberInterface $sub
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
     * @param mixed $value
     *
     * @return $this
     */
    public function setServiceContainer($value = null);
}
