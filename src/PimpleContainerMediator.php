<?php
/**
 * Contains PimpleContainerMediator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * with minimum dependencies so it is easy to drop in and use.
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
 * @author    Michael Cummings
 * <mgcummings@yahoo.com>
 */
namespace EventMediator;

use InvalidArgumentException;
use LogicException;
use Pimple\Container;

/**
 * Class PimpleContainerMediator
 *
 * @link http://pimple.sensiolabs.org/ Pimple
 */
class PimpleContainerMediator extends AbstractContainerMediator
{
    /**
     * @param Container|null $serviceContainer
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Container $serviceContainer = null)
    {
        $this->setServiceContainer($serviceContainer);
    }
    /**
     * @inheritdoc
     *
     * @param Container|null $value
     *
     * @link http://pimple.sensiolabs.org/ Pimple
     */
    public function setServiceContainer($value = null)
    {
        if (null === $value) {
            $value = new Container();
        }
        if (!$value instanceof Container) {
            $mess = sprintf(
                'Must be an instance of Pimple Container but given %s',
                gettype($value)
            );
            throw new InvalidArgumentException($mess);
        }
        $this->serviceContainer = $value;
    }
    /**
     * @inheritdoc
     *
     * @throws LogicException
     */
    protected function getServiceByName($serviceName)
    {
        return $this->getServiceContainer()[$serviceName];
    }
}
