<?php
/**
 * Contains MockEventTrait class.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\EventMediator;

use EventMediator\EventInterface;
use EventMediator\EventTrait;

/**
 * Class MockEventTrait
 */
class MockEventTrait implements EventInterface
{
    use EventTrait;
}
