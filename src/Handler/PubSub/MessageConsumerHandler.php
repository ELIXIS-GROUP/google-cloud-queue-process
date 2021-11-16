<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Handler\PubSub;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
use Google\Client as GoogleClient;

class MessageConsumerHandler
{
    /**
     * Send push message to worker.
     *
     * @version 1.1.0
     * @since 1.0.0
     **/
    public function pushConsumer(string $message, MessageHandlerBase $messageHandler): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);

        try {
            $result = $messageHandler('push', $message);

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
     * Send pull message to worker.
     *
     * @param string             $subscriptionName the Pub/Sub subscription name
     * @param MessageHandlerBase $messageHandler   pubSub class to consume and trait message
     * @version 1.2.0
     * @since 1.1.0
     **/
    public function pullConsumer(string $subscriptionName, MessageHandlerBase $messageHandler, PubSubService $pubSubService): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);

        $messageHandler->setSubscription($subscriptionName);
        $messages = $pubSubService->pullMessage($subscriptionName);

        try {
            foreach ($messages as $k => $message) {
                $messageHandler('pull', $message);
            }
            $result = 'Pull message for subscription \''.$subscriptionName.'\' was executed successfully.';

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
     * Consume Pub/Sub push message..
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function getRequestContent(Request $request, string $tokenEmail, string $tokenAudience, string $keyFilePath)
    {
        $requestContentJsonEncoded = $request->getContent();
        $authorization = $request->headers->get('authorization');

        $token = explode(' ', $authorization)[1];

        $googleClient = new GoogleClient([
            'keyFilePath' => $keyFilePath,
        ]);

        $payload = $googleClient->verifyIdToken($token);

        if ($payload) {
            $aud = $payload['aud'];
            $email = $payload['email'];
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);

            return $response;
        }

        if ($email != $tokenEmail && $aud != $tokenAudience) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);

            return $response;
        }

        $headers = json_encode($request->headers->all());
        $requestContent = json_decode($requestContentJsonEncoded, true);
        $message = base64_decode($requestContent['message']['data']);

        return $message;
    }
}
