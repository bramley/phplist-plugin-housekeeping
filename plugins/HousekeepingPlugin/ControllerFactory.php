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

use phpList\plugin\Common\Container;
use phpList\plugin\Common\ControllerFactoryBase;

/**
 * This class is a concrete implementation of ControllerFactoryBase.
 *
 * @category  phplist
 */
class ControllerFactory extends ControllerFactoryBase
{
    /**
     * Custom implementation to create a controller.
     * The instance of the controller is obtained through the DIC.
     *
     * @param string $pi     the plugin
     * @param array  $params further parameters from the URL
     *
     * @return Controller
     */
    public function createController($pi, array $params)
    {
        $depends = include __DIR__ . '/depends.php';
        $container = new Container($depends);
        $class = __NAMESPACE__ . '\\Controller';

        return $container->get($class);
    }
}
