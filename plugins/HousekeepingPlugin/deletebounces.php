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

// delete duplicate bounces from user_message_bounce
$query = <<<END
DELETE FROM {$tables['user_message_bounce']}
WHERE id NOT IN (
     SELECT * FROM (
        SELECT MIN(id)
        FROM {$tables['user_message_bounce']}
        GROUP BY user, message
    ) t
)
END;

Sql_Query($query);
$rows = Sql_Affected_Rows();
cl_output(sprintf('%d rows from the user_message_bounce table have been deleted', $rows));

// delete all bounces
Sql_Query("delete from {$tables['bounce']}");
$rows = Sql_Affected_Rows();
cl_output(sprintf('%d rows from the bounce table have been deleted', $rows));
