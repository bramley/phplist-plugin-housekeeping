<?php
/*
 * HousekeepingPlugin for phplist.
 *
 * This file is a part of HousekeepingPlugin.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2017 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/*
 * This is the entry code invoked by phplist.
 */
if (!$commandline) {
    $_GET['action'] = 'browser';
}
phpList\plugin\Common\Main::run(
    new phpList\plugin\HousekeepingPlugin\ControllerFactory()
);
