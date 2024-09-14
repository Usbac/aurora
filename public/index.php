<?php

require '../vendor/autoload.php';

$config = require(\Aurora\Core\Helper::getPath('app/bootstrap/config.php'));
(new \Aurora\Core\Kernel($config))->init(\Aurora\Core\Helper::getCurrentPath());
