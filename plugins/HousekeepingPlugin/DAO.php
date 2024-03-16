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
use phpList\plugin\Common\DAO\MessageTrait;

/**
 * DAO class that provides access to database.
 */
class DAO extends CommonDAO
{
    use MessageTrait;

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
            "DELETE b
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
     * Delete rows from the bounce table that are older than the parameter and and the related rows from the
     * user_message_bounce table.
     * Delete rows from the bounce table that are for blacklisted subscribers.
     * Delete rows from bounceregex_bounce that refer to non-existent bounces.
     *
     * @param string $interval the threshold for deletion
     *
     * @return array totals of the deletions
     */
    public function deleteBounces($interval)
    {
        $sql = <<<END
            DELETE umb
            FROM {$this->tables['user_message_bounce']} umb
            JOIN {$this->tables['bounce']} AS b ON b.id = umb.bounce
            WHERE DATE(b.date) < CURRENT_DATE() - INTERVAL $interval
END;
        $umbDeleted = $this->dbCommand->queryAffectedRows($sql);

        $sql = <<<END
            DELETE
            FROM {$this->tables['bounce']}
            WHERE DATE(date) < CURRENT_DATE() - INTERVAL $interval
END;
        $bouncesDeleted = $this->dbCommand->queryAffectedRows($sql);

        $sql = <<<END
            DELETE b
            FROM {$this->tables['bounce']} b
            JOIN {$this->tables['user_message_bounce']} umb on umb.bounce = b.id
            JOIN {$this->tables['user']} u on u.id = umb.user
            WHERE u.blacklisted = 1
END;
        $blacklistedDeleted = $this->dbCommand->queryAffectedRows($sql);

        $sql = <<<END
            DELETE umb
            FROM {$this->tables['user_message_bounce']} umb
            LEFT JOIN {$this->tables['bounce']} b ON umb.bounce = b.id
            WHERE b.id IS NULL
END;
        $umbOrphan = $this->dbCommand->queryAffectedRows($sql);

        $sql =
            "DELETE brb
            FROM {$this->tables['bounceregex_bounce']} AS brb
            LEFT JOIN {$this->tables['bounce']} AS b ON b.id = brb.bounce
            WHERE b.id IS NULL";

        $bounceRegexDeleted = $this->dbCommand->queryAffectedRows($sql);

        return [$bouncesDeleted, $umbDeleted, $blacklistedDeleted, $umbOrphan, $bounceRegexDeleted];
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

    /**
     * Delete rows from the user_history table that are older than the parameter from the most
     * recent user history row for each subscriber.
     *
     * @return int the number of rows deleted
     */
    public function deleteUserHistory($interval)
    {
        $sql =
            "DELETE uh
            FROM {$this->tables['user_history']}  uh
            JOIN (
                SELECT userid, max(date) AS maxdate
                FROM {$this->tables['user_history']}
                GROUP BY userid
            ) AS t2 ON t2.userid = uh.userid
            WHERE uh.date < t2.maxdate - INTERVAL $interval";

        return $this->dbCommand->queryAffectedRows($sql);
    }

    /**
     * Test whether there is already a housekeeping process running.
     *
     * @return array|null
     */
    public function checkLock($page)
    {
        $sql = <<<END
            SELECT id, NOW() - modified AS age
            FROM {$this->tables['sendprocess']}
            WHERE page = "$page" AND alive
            ORDER BY started DESC
END;
        $row = $this->dbCommand->queryRow($sql);

        return $row;
    }

    /**
     * Set an existing row to not be alive so that the housekeeping process will eventually stop itself.
     *
     * @return int
     */
    public function deleteLock($id)
    {
        $sql = <<<END
            UPDATE {$this->tables['sendprocess']}
            SET alive = 0
            WHERE id = $id
END;
        $rowsDeleted = $this->dbCommand->queryAffectedRows($sql);

        return $rowsDeleted;
    }
}
