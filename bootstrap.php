<?php
/**
 * Contains auto loader bootstrap.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Event Mediator - A general event mediator (dispatcher)
 * with minimum dependencies so it is easy to drop in and use.
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
/*
 * Turn off warning messages for the following includes.
 */
$errorReporting = error_reporting(E_ALL & ~E_WARNING);
/*
 * Find auto loader from one of
 * vendor/bin/
 * OR ./
 * OR bin/
 * OR src/Project/
 * OR vendor/Project/Project/
 */
(include_once dirname(__DIR__) . '/autoload.php')
|| (include_once __DIR__ . '/vendor/autoload.php')
|| (include_once dirname(__DIR__) . '/vendor/autoload.php')
|| (include_once dirname(dirname(__DIR__)) . '/vendor/autoload.php')
|| (include_once dirname(dirname(dirname(__DIR__))) . '/autoload.php');
error_reporting($errorReporting);
unset($errorReporting);
if (!class_exists('\\Composer\\Autoload\\ClassLoader', false)) {
    if ('cli' === PHP_SAPI) {
        $mess = 'Could NOT find required Composer class auto loader. Aborting ...';
        fwrite(STDERR, $mess);
    }
    return 1;
}
