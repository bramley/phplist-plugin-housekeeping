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

use HousekeepingPlugin;
use phpList\plugin\Common\Controller as CommonController;
use phpList\plugin\Common\PageURL;

/**
 * This class is the controller for the plugin providing the action methods.
 */
class Controller extends CommonController
{
    private $dao;
    private $context;

    public function __construct($dao, $context)
    {
        $this->dao = $dao;
        $this->context = $context;
        parent::__construct();
    }

    public function actionBrowser()
    {
        if (isset($_POST['submit'])) {
            if ($_POST['submit'] == 'stop') {
                $this->dao->deleteLock($_POST['row_id']);
                header('Location: ' . new PageURL());
                exit;
            }

            if ($_POST['submit'] == 'process') {
                $this->process();
            }

            return;
        }

        if ($row = $this->dao->checkLock(HousekeepingPlugin::PROCESS_PAGE)) {
            $formBody = <<<END
        <p>A housekeeping process, id {$row['id']}, is already running and was alive {$row['age']} seconds ago.</p>
        <input name="row_id" type="hidden" value="{$row['id']}">
        <button name="submit" value="stop">Stop the process</button>
END;
        } else {
            $prompt = s('To run the housekeeping process click the Process button');
            $formBody = <<<END
        <p>$prompt</p>
        <button name="submit" value="process">Process</button>
END;
        }
        echo <<<END
        <a class="button" target="_blank" href="./?page=configure#housekeeping">Configuration settings</a>
        <form method="post">
        $formBody
        </form>
END;
    }

    public function actionDefault()
    {
        $this->process();
    }

    private function process()
    {
        $this->context->start();
        $processId = getPageLock();

        if (!$processId) {
            $this->context->output(s('Unable get lock for processing'));

            return;
        }

        try {
            /*
             * Process messages
             */
            $age = getConfig('housekeeping_message_age');

            if ($interval = $this->validateInterval($age)) {
                $campaigns = $this->dao->selectOldCampaigns($interval);

                if (count($campaigns) > 0) {
                    foreach ($campaigns as $c) {
                        $id = $c['id'];
                        $subject = $c['subject'];

                        if ($r = $this->dao->deleteMessage($id)) {
                            $event = s('Campaign %d %s deleted', $id, $subject);
                            $this->logEvent($event);
                            $this->context->output($event);
                        }
                        $this->keepLock($processId);
                    }
                } else {
                    $this->context->output(s('No campaigns older than %s to delete', $age));
                }
            }

            /*
             * Process event log
             */
            $age = getConfig('housekeeping_event_log_age');

            if ($interval = $this->validateInterval($age)) {
                $deletedCount = $this->dao->trimEventLog($interval);

                if ($deletedCount > 0) {
                    $event = s('%d rows deleted from the event log', $deletedCount);
                    $this->logEvent($event);
                    $this->context->output($event);
                } else {
                    $this->context->output(s('No event log rows older than %s to delete', $age));
                }
                $this->keepLock($processId);
            }

            /*
             * Process bounces
             */
            $age = getConfig('housekeeping_bounces_age');

            if ($interval = $this->validateInterval($age)) {
                list($bounces, $umb, $blacklisted, $regexBounce) = $this->dao->deleteBounces($interval);

                if ($bounces > 0) {
                    $event = s('%d bounce rows deleted', $bounces);
                    $this->logEvent($event);
                    $this->context->output($event);
                } else {
                    $this->context->output(s('No bounce rows older than %s to delete', $age));
                }

                if ($umb > 0) {
                    $event = s('%d user_message_bounce rows deleted', $umb);
                    $this->logEvent($event);
                    $this->context->output($event);
                } else {
                    $this->context->output(s('No user_message_bounce rows older than %s to delete', $age));
                }

                if ($blacklisted > 0) {
                    $event = s('%d bounce rows for blacklisted subscribers deleted', $blacklisted);
                    $this->logEvent($event);
                    $this->context->output($event);
                }

                if ($regexBounce > 0) {
                    $event = s('%d rows deleted from the bounceregex_bounce table', $regexBounce);
                    $this->logEvent($event);
                    $this->context->output($event);
                } else {
                    $this->context->output(s('No rows to delete from bounceregex_bounce'));
                }
                $this->keepLock($processId);
            }

            /*
             * Process forward urls
             */
            if (getConfig('housekeeping_unused_forwards')) {
                $deletedCount = $this->dao->deleteUnusedForwards();

                if ($deletedCount > 0) {
                    $event = s('%d rows deleted from the linktrack_forward table', $deletedCount);
                    $this->logEvent($event);
                    $this->context->output($event);
                } else {
                    $this->context->output(s('No rows to delete from linktrack_forward'));
                }
                $this->keepLock($processId);
            }

            /*
             * Process user history
             */
            $age = getConfig('housekeeping_user_history_age');

            if ($interval = $this->validateInterval($age)) {
                $deletedCount = $this->dao->deleteUserHistory($interval);

                if ($deletedCount > 0) {
                    $event = s('%d rows deleted from the user history table', $deletedCount);
                    $this->logEvent($event);
                    $this->context->output($event);
                } else {
                    $this->context->output(s('No user history rows older than %s to delete', $age));
                }
                $this->keepLock($processId);
            }
        } catch (\Exception $e) {
            $this->context->output($e->getMessage());
            // continue
        }
        releaseLock($processId);
        $this->context->finish();
    }

    private function validateInterval($interval)
    {
        return preg_match('/^(\d+\s+(day|week|month|quarter|year))s?$/i', trim($interval), $matches)
            ? $matches[1]
            : false;
    }

    private function keepLock($processId)
    {
        if (!checkLock($processId)) {
            throw new \Exception('Process killed by another process');
        }
        keepLock($processId);
    }
}
