<?php

require __DIR__.'/vendor/sllh/php-cs-fixer-styleci-bridge/autoload.php';

use SLLH\StyleCIBridge\ConfigBridge;

$config = ConfigBridge::create();
$config->setUsingCache(true);

if (method_exists($config, 'setRiskyAllowed')) {
    $config->setRiskyAllowed(true);
}

return $config;
