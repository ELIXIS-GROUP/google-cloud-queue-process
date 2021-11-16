<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Handler\CloudTasks;

use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksMessageService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.1.0
 * @since 1.1.0
 **/
class MessageConsumerHandler
{
    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function consumer(string $message, MessageHandlerBase $messageHandler): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        try {
            $message = new CloudTasksMessageService($message);
            $result = $messageHandler($message);

            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent(json_encode([
                'code' => Response::HTTP_OK,
                'message' => $result,
                'status' => 'HTTP_OK',
            ]));
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_CONFLICT);
            $response->setContent(json_encode(['error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => $e->getMessage(),
                'status' => 'HTTP_CONFLICT',
            ]]));
        }

        return $response;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function getRequestContent(Request $request): string
    {
        $message = $request->getContent();

        return $message;
    }
}
