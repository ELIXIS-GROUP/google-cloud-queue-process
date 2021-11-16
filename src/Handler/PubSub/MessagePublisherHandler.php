<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Handler\PubSub;

use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
use Symfony\Component\HttpFoundation\Response;

class MessagePublisherHandler
{
    public const TYPE_PULL = 'pull';
    public const TYPE_PUSH = 'push';

    /**
     * Publish a new Pub/Sub message.
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function publishMessage(string $message, string $type, string $topic, string $subscription, PubSubService $pubSubService): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);

        try {
            $reponseMessage = [
                'code' => Response::HTTP_OK,
                'message' => 'A new message is published in the "'.$type.'" subscription : "'.$subscription.'"',
                'status' => 'HTTP_OK',
            ];

            if (self::TYPE_PULL == $type) {
                $result = $pubSubService->publishPullMessage($topic, $subscription, $message);
                $reponseMessage = array_merge($reponseMessage, $result->getInfo());
            } elseif (self::TYPE_PUSH == $type) {
                $result = $pubSubService->publishPushMessage($topic, $subscription, $message);
                $reponseMessage = array_merge($reponseMessage, $result->getInfo());
            } else {
                $response->setStatusCode(Response::HTTP_CONFLICT);
                $reponseMessage = [
                    'code' => Response::HTTP_CONFLICT,
                    'message' => 'This type used : "'.$type.'", is not a valid type use push or pull.',
                    'status' => 'HTTP_CONFLICT',
                ];
            }

            $response->setContent(json_encode($reponseMessage));
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_CONFLICT);
            $response->setContent($e->getMessage(), response::HTTP_CONFLICT);
        }

        return $response;
    }
}
