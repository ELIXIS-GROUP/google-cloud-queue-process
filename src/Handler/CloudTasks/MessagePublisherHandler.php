<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Handler\CloudTasks;

use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksService;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.1.0
 * @since 1.1.0
 **/
class MessagePublisherHandler
{
    /**
     * Publish a nex tasks.
     *
     * @return JsonResponse
     *
     * @since 1.1.0
     * @version 1.1.0
     **/
    public function publishtask(string $message, string $queueName, string $urlTaskHandler, CloudTasksService $cloudTasksService): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);

        try {
            $taskResult = $cloudTasksService->createTask($message, $queueName, $urlTaskHandler);

            if (null !== $taskResult->getInfo()) {
                $info = $taskResult->getInfo();

                $response->setStatusCode($info['info']['code']);
                $response->setContent(json_encode($info));
            } else {
                $error = $taskResult->getError();
                throw new \Exception(json_encode($error), $error['error']['code']);
            }
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_CONFLICT);
            $response->setContent($e->getMessage(), response::HTTP_CONFLICT);
        }

        return $response;
    }
}
