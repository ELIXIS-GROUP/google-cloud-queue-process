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
     * @param string             $subscriptionName
     * @param string             $topicName
     * @param MessageHandlerBase $messageHandler
     * @param PubSubService      $pubSubService
     * @param Array              $pullOptions
     * 
     * @version 1.3.0
     * @since 1.1.0
     **/
    public function pullConsumer(string $subscriptionName, string $topicName, MessageHandlerBase $messageHandler, PubSubService $pubSubService, Array $pullOptions = []): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);

        $messageHandler->setSubscription($subscriptionName);
        $messageHandler->setTopic($topicName);

        $messages = $pubSubService->pullMessage($subscriptionName, $pullOptions);

        try {
            foreach ($messages as $k => $message) {
                try{
                    $messageHandler('pull', $message);
                } catch (\Exception $e) {
                    $pubSubService->publishMessage($messageHandler->getTopic(), $message->data());
                    //throw new \Exception('Error in processedData method, this message is publish again with type "pull"');
                }
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
