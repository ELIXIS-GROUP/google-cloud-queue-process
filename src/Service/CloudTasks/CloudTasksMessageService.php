<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\CloudTasks;

class CloudTasksMessageService
{
    /**
     * @var string
     **/
    private $_contentMessage;

    public function __construct(string $contentMessage)
    {
        $this->_contentMessage = $contentMessage;
    }

    public function getContent()
    {
        return $this->_contentMessage;
    }
}
