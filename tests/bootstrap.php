<?php

require __DIR__.'/../vendor/autoload.php';

if (PHP_VERSION_ID >= 80500) {
    error_reporting(error_reporting() & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}
