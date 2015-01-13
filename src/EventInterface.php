<?php
/**
 * EventInterface.php
 *
 * PHP version 5.4
 *
 * @since  20150112 11:04
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace EventMediator;

/**
 * Class Event
 */
interface EventInterface
{
    /**
     * Let's dispatcher know event was handled.
     *
     * @return $this
     */
    public function eventHandled();
    /**
     * Checks if event was handled.
     *
     * @return bool
     */
    public function hasHandled();
}
