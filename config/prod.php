<?php

// configure your app for the production environment
$sendBin        = '/opt/raspberry-remote/send';
$dataFile       = __DIR__.'/../data/data.json';
$switchFile     = __DIR__.'/../data/toSwitch.json';
$logFile        = __DIR__.'/../logs/switchIt.log';
$app['version'] = '0.9';
