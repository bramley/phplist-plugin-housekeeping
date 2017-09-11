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

/**
 * Registers the plugin with phplist.
 */
class HousekeepingPlugin extends phplistPlugin
{
    const VERSION_FILE = 'version.txt';

    /*
     *  Inherited variables
     */
    public $name = 'Database housekeeping';
    public $authors = 'Duncan Cameron';
    public $description = 'Selectively delete old data from the phplist database';
    public $commandlinePluginPages = array('process');
    public $topMenuLinks = array(
        'process' => array('category' => 'system'),
    );

    public $pageTitles = array(
        'process' => 'Housekeeping',
    );

    public $settings = array(
        'housekeeping_message_age' => array(
            'description' => 'Threshold (in months) for deleting campaigns. Set to 0 to disable this function.',
            'type' => 'integer',
            'value' => 0,
            'min' => 0,
            'max' => 50,
            'allowempty' => 1,
            'category' => 'Housekeeping',
        ),
        'housekeeping_event_log_age' => array(
            'description' => 'Threshold (in months) for deleting event log records. Set to 0 to disable this function.',
            'type' => 'integer',
            'value' => 0,
            'min' => 0,
            'max' => 50,
            'allowempty' => 1,
            'category' => 'Housekeeping',
        ),
        'housekeeping_bounces_age' => array(
            'description' => 'Threshold (in days) for deleting bounce records. Set to 0 to disable this function.',
            'type' => 'integer',
            'value' => 0,
            'min' => 0,
            'max' => 365,
            'allowempty' => 1,
            'category' => 'Housekeeping',
        ),
        'housekeeping_unused_forwards' => array(
            'description' => 'Whether to delete rows from linktrack_forward that are not used.',
            'type' => 'boolean',
            'value' => 0,
            'allowempty' => true,
            'category' => 'Housekeeping',
        ),
    );

    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . __CLASS__ . '/';
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
        parent::__construct();
    }

    public function adminmenu()
    {
        return $this->pageTitles;
    }

    /**
     * Provide the dependencies for enabling this plugin.
     *
     * @return array
     */
    public function dependencyCheck()
    {
        global $plugins;

        return array(
            'Common Plugin v3.6.7 or later installed' => (
                phpListPlugin::isEnabled('CommonPlugin')
                && version_compare($plugins['CommonPlugin']->version, '3.6.7') >= 0
            ),
            'PHP version 5.4 or greater' => version_compare(PHP_VERSION, '5.4') > 0,
        );
    }
}
