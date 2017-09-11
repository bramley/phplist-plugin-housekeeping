<?php
/**
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

namespace phpList\plugin\HousekeepingPlugin;

use phpList\plugin\Common\DAO as CommonDAO;

/**
 * DAO class that provides access to database.
 */
class DAO extends CommonDAO
{
    /**
     * Select campaigns that are older than the parameter.
     *
     * @param string $interval the threshold for deletion
     *
     * @return int the number of campaigns selected
     */
    public function selectOldCampaigns($interval)
    {
        $sql =
            "SELECT id, subject
            FROM {$this->tables['message']}
            WHERE status = 'sent' AND sent < CURRENT_DATE() - INTERVAL $interval
            ORDER BY id";

        return $this->dbCommand->queryAll($sql);
    }

    public function deleteUnlinkedBounces($interval)
    {
        $sql =
            "DELETE
            FROM {$this->tables['bounce']} AS b
            LEFT JOIN {$this->tables['user_message_bounce']} AS umb ON b.id = umb.bounce
            WHERE umb.bounceid IS NULL
            AND DATE(b.date) < CURRENT_DATE() - INTERVAL $interval";

        return $this->dbCommand->queryAffectedRows($sql);
    }

    /**
     * Delete rows from the event log that are older than the parameter.
     *
     * @param string $interval the threshold for deletion
     *
     * @return int the number of rows deleted
     */
    public function trimEventLog($interval)
    {
        $sql =
            "DELETE
            FROM {$this->tables['eventlog']}
            WHERE DATE(entered) < CURRENT_DATE() - INTERVAL $interval";

        return $this->dbCommand->queryAffectedRows($sql);
    }

    /**
     * Delete rows from the bounce and user_message_bounce tables that are older than the parameter.
     * Also delete rows from bounceregex_bounce that refer to non-existent bounces.
     *
     * @param string $interval the threshold for deletion
     *
     * @return array totals of the three deletions
     */
    public function deleteBounces($interval)
    {
        $sql =
            "DELETE
            FROM {$this->tables['bounce']}
            WHERE DATE(date) < CURRENT_DATE() - INTERVAL $interval";

        $bouncesDeleted = $this->dbCommand->queryAffectedRows($sql);

        $sql =
            "DELETE
            FROM {$this->tables['user_message_bounce']}
            WHERE DATE(time) < CURRENT_DATE() - INTERVAL $interval";

        $umbDeleted = $this->dbCommand->queryAffectedRows($sql);

        $sql =
            "DELETE brb
            FROM {$this->tables['bounceregex_bounce']} AS brb
            LEFT JOIN {$this->tables['bounce']} AS b ON b.id = brb.bounce
            WHERE b.id IS NULL";

        $bounceRegexDeleted = $this->dbCommand->queryAffectedRows($sql);

        return [$bouncesDeleted, $umbDeleted, $bounceRegexDeleted];
    }

    /**
     * Delete rows from the forward table that are not referenced by the forward_ml
     * and forward_uml_click tables.
     *
     * @return int the number of rows deleted
     */
    public function deleteUnusedForwards()
    {
        $sql =
            "DELETE fw
            FROM {$this->tables['linktrack_forward']} AS fw
            LEFT JOIN {$this->tables['linktrack_ml']} AS ml ON ml.forwardid = fw.id
            LEFT JOIN {$this->tables['linktrack_uml_click']} AS uml ON uml.forwardid = fw.id
            WHERE ml.forwardid IS NULL AND uml.forwardid IS NULL";

        return $this->dbCommand->queryAffectedRows($sql);
    }
}
