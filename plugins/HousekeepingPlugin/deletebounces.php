<?php
/**
 * HousekeepingPlugin for phplist.
 *
 * This file is a part of HousekeepingPlugin.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2020 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/*
 * Delete all bounces by truncating the bounce table
 */

Sql_Query(sprintf('truncate %s', $tables['bounce']));
cl_output('All bounces have been deleted');
