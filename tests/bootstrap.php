<?php

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

//require dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new Application($kernel);
};