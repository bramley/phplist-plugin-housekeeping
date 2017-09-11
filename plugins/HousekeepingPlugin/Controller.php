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
use phpList\plugin\Common\Context;

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
        echo <<<'END'
        <form method="post">
            <p>To run the housekeeping process click the Submit button</p>
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
        $months = getConfig('housekeeping_message_age');

        if ($months > 0) {
            $campaigns = $this->dao->selectOldCampaigns($months);

            if (count($campaigns) > 0) {
                foreach ($campaigns as $c) {
                    $id = $c['id'];
                    $subject = $c['subject'];

                    if ($r = $this->messageDao->deleteMessage($id)) {
                        $event = sprintf('Campaign %d %s deleted', $id, $subject);
                        $this->logEvent($event);
                        $this->context->output($event);
                    }
                    break;
                }
            } else {
                $this->context->output("No campaigns older than $months months to delete");
            }
        }

        /*
         * Process event log
         */
        $months = getConfig('housekeeping_event_log_age');

        if ($months > 0) {
            $deletedCount = $this->dao->trimEventLog($months);

            if ($deletedCount > 0) {
                $event = sprintf('%d rows deleted from the event log', $deletedCount);
                $this->logEvent($event);
                $this->context->output($event);
            } else {
                $this->context->output("No event log rows older than $months months to delete");
            }
        }

        /*
         * Process bounces
         */
        $days = getConfig('housekeeping_bounces_age');

        if ($days > 0) {
            list($bounces, $umb, $regexBounce) = $this->dao->deleteBounces($days);

            if ($bounces > 0) {
                $event = sprintf('%d bounce rows deleted', $bounces);
                $this->logEvent($event);
                $this->context->output($event);
            } else {
                $this->context->output("No bounce rows older than $days days to delete");
            }

            if ($umb > 0) {
                $event = sprintf('%d user_message_bounce rows deleted', $umb);
                $this->logEvent($event);
                $this->context->output($event);
            } else {
                $this->context->output("No user_message_bounce rows older than $days days to delete");
            }

            if ($regexBounce > 0) {
                $event = sprintf('%d rows deleted from the bounceregex_bounce table', $umb);
                $this->logEvent($event);
                $this->context->output($event);
            } else {
                $this->context->output('No rows to delete from bounceregex_bounce');
            }
        }

        /*
         * Process forward urls
         */
        if (getConfig('housekeeping_unused_forwards')) {
            $deletedCount = $this->dao->deleteUnusedForwards();

            if ($deletedCount > 0) {
                $event = sprintf('%d rows deleted from the linktrack_forward table', $deletedCount);
                $this->logEvent($event);
                $this->context->output($event);
            } else {
                $this->context->output('No rows to delete from linktrack_forward');
            }
        }
        $this->context->finish();
    }
}
