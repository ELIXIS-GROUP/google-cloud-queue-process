<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Handler\CloudTasks;

use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksMessageService;

/**
 * Cloud task message handler, to process task message.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method __invoke Processing CloudTasks message.
 *
 * @version 1.1.0
 * @since 1.1.0
 **/
class MessageHandlerBase
{
    /**
     * @inherirtDoc CloudTasksMessageService $cloudTasksMessageService
     *
     * @return void
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function __construct()
    {
    }

    /**
     * Processing CloudTasks message.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function __invoke(CloudTasksMessageService $cloudTasksMessageService)
    {
        $decodeMessage = json_decode($cloudTasksMessageService->getContent(), true);
        if ($decodeMessage) {
            return $this->processedData($decodeMessage);
        }
    }

    /**
     * Processing data.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function processedData(array $data)
    {
        return $data;
    }
}
