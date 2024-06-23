<?php

namespace App\Config;

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

class Bootstrap {
    public static function loadEnv()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }
}