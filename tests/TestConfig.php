<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest;

class TestConfig
{
    /**
     * @var string
     **/
    public const GOOGLE_KEYFILEPATH = './config/google/message-queue-processor.json';

    /**
     * @var string
     **/
    public const GOOGLE_CLOUDTASKS_LOCATION_ID = 'europe-west1';
}
