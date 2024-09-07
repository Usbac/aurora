<?php

require '../vendor/autoload.php';

ini_set('error_log', \Aurora\Core\Helper::getPath('aurora.log'));
session_start();

$config = require(\Aurora\Core\Helper::getPath('app/bootstrap/config.php'));
(new \Aurora\Core\Kernel($config))->init(\Aurora\Core\Helper::getCurrentPath());
