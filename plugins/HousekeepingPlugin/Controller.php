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

use phpList\plugin\Common\Controller as CommonController;

/**
 * This class is the controller for the plugin providing the action methods.
 */
class Controller extends CommonController
{
    private $dao;
    private $messageDao;
    private $context;

    public function __construct($dao, $messageDao, $context)
    {
        $this->dao = $dao;
        $this->messageDao = $messageDao;
        $this->context = $context;
        parent::__construct();
    }

    public function actionBrowser()
    {
        if (isset($_POST['submit'])) {
            $this->process();

            return;
        }
        $prompt = s('To run the housekeeping process click the Submit button');
        echo <<<END
        <form method="post">
            <p>$prompt</p>
            <input type="submit" name="submit" value="Submit"/>
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

                    if ($r = $this->messageDao->deleteMessage($id)) {
                        $event = s('Campaign %d %s deleted', $id, $subject);
                        $this->logEvent($event);
                        $this->context->output($event);
                    }
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
        }

        /*
         * Process bounces
         */
        $age = getConfig('housekeeping_bounces_age');

        if ($interval = $this->validateInterval($age)) {
            list($bounces, $umb, $regexBounce) = $this->dao->deleteBounces($interval);

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

            if ($regexBounce > 0) {
                $event = s('%d rows deleted from the bounceregex_bounce table', $regexBounce);
                $this->logEvent($event);
                $this->context->output($event);
            } else {
                $this->context->output(s('No rows to delete from bounceregex_bounce'));
            }
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
        }
        $this->context->finish();
    }

    private function validateInterval($interval)
    {
        return preg_match('/^(\d+\s+(day|week|month|quarter|year))s?$/i', trim($interval), $matches)
            ? $matches[1]
            : false;
    }
}
