<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\CloudTasks;

use Google\Cloud\Tasks\V2\Queue;

trait CloudTasksQueueService
{
    public function createQueue(string $queueName)
    {
        $cloudTasksClient = $this->cloudTasksClient();
        $locationName = $cloudTasksClient::locationName($this->_projectId, $this->_locationId);
        $ressourceQueueName = $cloudTasksClient::queueName($this->_projectId, $this->_locationId, $queueName);

        $queue = new Queue([
            'name' => $ressourceQueueName,
        ]);

        $result = $cloudTasksClient->createQueue($locationName, $queue);

        return $result->getName();
    }

    public function listQueues(): array
    {
        $queues = [];

        $cloudTasksClient = $this->cloudTasksClient();
        $locationName = $cloudTasksClient::locationName($this->_projectId, $this->_locationId);
        $listQueues = $cloudTasksClient->listQueues($locationName);

        foreach ($listQueues->iterateAllElements() as $k => $queue) {
            array_push($queues, $queue->getName());
        }

        return $queues;
    }

    public function ifQueueExist(string $queueName): bool
    {
        $cloudTasksClient = $this->cloudTasksClient();
        $locationName = $cloudTasksClient::locationName($this->_projectId, $this->_locationId);
        $ressourceQueueName = $cloudTasksClient::queueName($this->_projectId, $this->_locationId, $queueName);

        return in_array($ressourceQueueName, $this->listQueues());
    }

    public function deleteQueue(string $queueName): void
    {
        $cloudTasksClient = $this->cloudTasksClient();
        $ressourceQueueName = $cloudTasksClient::queueName($this->_projectId, $this->_locationId, $queueName);

        $result = $cloudTasksClient->deleteQueue($ressourceQueueName);

        return;
    }
}
