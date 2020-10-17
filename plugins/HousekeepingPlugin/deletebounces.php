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

// delete all bounces
Sql_Query("delete from {$tables['bounce']}");
$rows = Sql_Affected_Rows();
cl_output(sprintf('%d rows from the bounce table have been deleted', $rows));
