<?php

require '../vendor/autoload.php';

ini_set('error_log', \Aurora\System\Helper::getPath('aurora.log'));
session_start();

$config = require(\Aurora\System\Helper::getPath('bootstrap/config.php'));
(new \Aurora\System\Kernel($config))->init(\Aurora\System\Helper::getCurrentPath());
