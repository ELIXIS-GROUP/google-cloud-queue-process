<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\CloudTasks;

use Google\Cloud\Tasks\V2\AppEngineHttpRequest;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\Task;
use Symfony\Component\HttpFoundation\Response;

/**
 * Message bus for CloudTasks, for more information see doc.
 * @see https://cloud.google.com/tasks/docs/creating-appengine-tasks?hl=fr
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method cloudTasksClient Init Cloud Tasks client.
 * @method createTask Create a new task.
 * @method _createHttpRequest Defines the HTTP request that is sent to an App Engine app when the task is dispatched.
 *
 * @version 1.1.0
 * @since 1.1.0
 **/
class CloudTasksService
{
    use CloudTasksQueueService;

    /**
     * @var string
     **/
    private $_keyFilePath;

    /**
     * @var string
     **/
    private $_projectId;

    /**
     * @var string
     **/
    private $_locationId;

    /**
     * @var array
     **/
    private $_info = null;

    /**
     * @var array
     **/
    private $error = null;

    public function __construct(string $projectId, string $keyFilePath, string $locationId)
    {
        $this->_projectId = $projectId;
        $this->_keyFilePath = $keyFilePath;
        $this->_locationId = $locationId;
    }

    /**
     * Init Cloud Tasks client.
     *
     * @return Google\Cloud\Tasks\V2\CloudTasksClient
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function cloudTasksClient(): CloudTasksClient
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->_keyFilePath);

        return new CloudTasksClient();
    }

    /**
     * Create a new task.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function createTask(string $message, string $queueName, string $urlTaskHandler): CloudTasksService
    {
        try {
            $task = new Task();
            $cloudTasksClient = $this->cloudTasksClient();
            $httpRequest = $this->_createHttpRequest($message, $urlTaskHandler);

            $task->setAppEngineHttpRequest($httpRequest);

            if (!$this->ifQueueExist($queueName)) {
                $ressourceQueueName = $this->createQueue($queueName);
            } else {
                $ressourceQueueName = $cloudTasksClient::queueName($this->_projectId, $this->_locationId, $queueName);
            }

            $result = $cloudTasksClient->createTask($ressourceQueueName, $task);

            $this->_setInfo($ressourceQueueName, $result->getName());
        } catch (\Exception $e) {
            $errorInitialMessage = json_decode($e->getMessage());
            $errorMessage = (isset($errorInitialMessage->message)) ? $errorInitialMessage->message : $e->getMessage();

            throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, $errorMessage, 'HTTP_CONFLICT')));
        }

        return $this;
    }

    /**
     * Defines the HTTP request that is sent to an App Engine app when the task is dispatched.
     * @see https://cloud.google.com/tasks/docs/reference/rest/v2/projects.locations.queues.tasks#appenginehttprequest
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    private function _createHttpRequest(string $message, string $urlTaskHandler): AppEngineHttpRequest
    {
        $content = $content ?? [];

        $httpRequest = new AppEngineHttpRequest();

        $httpRequest->setRelativeUri($urlTaskHandler);
        $httpRequest->setHeaders(['Content-type' => 'application/json']);
        $httpRequest->setHttpMethod(HttpMethod::POST);
        $httpRequest->setBody($message);

        return $httpRequest;
    }

    /**
     * Set info for CloudTasks action.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    private function _setInfo(string $queueName, string $taskName): CloudTasksService
    {
        $this->_info = [
            'info' => [
                'code' => Response::HTTP_CREATED,
                'message' => [
                    'queueName' => $queueName,
                    'taskName' => $taskName,
                    'projectId' => $this->_projectId,
                ],
                'status' => 'HTTP_CREATED',
            ],
        ];

        return $this;
    }

    /**
     * Get info for CloudTasks action.
     *
     * @return CloudTasksService
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function getInfo(): ?array
    {
        return $this->_info;
    }

    /**
     * Set error for CloudTasks action.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    private function _error(int $httpCode, string $message, string $status): array
    {
        return [
            'error' => [
                'code' => $httpCode,
                'message' => $message,
                'status' => $status,
            ],
        ];
    }
}
