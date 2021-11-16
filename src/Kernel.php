<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess;

use Symfony\Component\Dotenv\Dotenv;

class Kernel
{
    /**
     * Load dotenv package.
     *
     * @throws \Exception
     *
     * @since 1.0.0-dev
     *
     * @version 1.0.0-dev
     **/
    public static function loadDotEnv()
    {
        if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
            if (!class_exists(Dotenv::class)) {
                throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
            }
            (new Dotenv())->load(__DIR__.'/../.env');
        }

        $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
    }
}
