<?php

$pluginsDir = dirname(__DIR__);

return [
    'phpList\plugin\HousekeepingPlugin\Controller' => $pluginsDir . '/HousekeepingPlugin/Controller.php',
    'phpList\plugin\HousekeepingPlugin\ControllerFactory' => $pluginsDir . '/HousekeepingPlugin/ControllerFactory.php',
    'phpList\plugin\HousekeepingPlugin\DAO' => $pluginsDir . '/HousekeepingPlugin/DAO.php',
];
