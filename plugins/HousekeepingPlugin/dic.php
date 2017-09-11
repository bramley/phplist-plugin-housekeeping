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
 * This file creates a dependency injection container.
 */
use phpList\plugin\Common\Container;
use phpList\plugin\HousekeepingPlugin\DAO;
use Psr\Container\ContainerInterface;

return new Container([
    'phpList\plugin\HousekeepingPlugin\Controller' => function (ContainerInterface $container) {
        return new phpList\plugin\HousekeepingPlugin\Controller(
            $container->get('DAO'),
            $container->get('phpList\plugin\Common\DAO\Message'),
            $container->get('phpList\plugin\Common\Context')
        );
    },
    'DAO' => function (ContainerInterface $container) {
        return new DAO(
            $container->get('phpList\plugin\Common\DB')
        );
    },
]);
