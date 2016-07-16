<?php
/**
 * Contains auto loader bootstrap.
 *
 * PHP version 5.6
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
/*
 * Nothing to do if Composer auto loader already exists.
 */
if (class_exists('\\Composer\\Autoload\\ClassLoader', false)) {
    return;
}
/*
 * Find Composer auto loader after striping away any vendor path.
 */
$path = str_replace('\\', '/', __DIR__);
$vendorPos = strpos($path, 'vendor/');
if (false !== $vendorPos) {
    $path = substr($path, 0, $vendorPos);
}
/*
 * Turn off warning messages for the following include.
 */
$errorReporting = error_reporting(E_ALL & ~E_WARNING);
include_once $path . '/vendor/autoload.php';
error_reporting($errorReporting);
unset($errorReporting, $path, $vendorPos);
if (!class_exists('\\Composer\\Autoload\\ClassLoader', false)) {
    $mess = 'Could NOT find required Composer class auto loader. Aborting ...';
    if ('cli' === PHP_SAPI) {
        fwrite(STDERR, $mess);
    } else {
        fwrite(STDOUT, $mess);
    }
    return 1;
}
