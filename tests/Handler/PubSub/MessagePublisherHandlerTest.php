<?php

declare(strict_types=1);

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest\Handler\PubSub;

use PHPUnit\Framework\TestCase;
use GoogleCloudQueueProcess\Handler\PubSub\MessagePublisherHandler;
use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
use GcpQueueProcessTest\TestConfig;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test class MessagePublisherHandler.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method setUpBeforeClass
 * @method testPublishPushMessage
 * @method testPublishUnknowType
 * @method testPublishTopicAndSubsciptionWithNotValidFormat
 *
 * @version 1.2.1
 * @since 1.1.0
 **/
class MessagePublisherHandlerTest extends TestCase
{
    /**
     * @var string
     **/
    public const TOPIC = 'test-topic';

    /**
     * @var string
     **/
    public const SUBCRIPTION = 'test-sub';

    /**
     * @var MessagePublisherHandler
     **/
    protected static $messagePublisher;

    /**
     * @var PubSubService
     **/
    protected static $pubSubService;

    public static function setUpBeforeClass(): void
    {
        self::$messagePublisher = new MessagePublisherHandler();
        self::$pubSubService = new PubSubService($_ENV['GOOGLE_PROJECT_ID'], TestConfig::GOOGLE_KEYFILEPATH);

        return;
    }

    public function testPublishPushMessage(): void
    {
        $response = self::$messagePublisher->publishMessage('message pus sub', 'pull', self::TOPIC, self::SUBCRIPTION, self::$pubSubService);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'code' => Response::HTTP_OK,
            'message' => 'A new message is published in the "pull" subscription : "'.self::SUBCRIPTION.'"',
            'status' => 'HTTP_OK',
            'info' => [
                'code' => 201,
                'message' => [
                    'topicName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/topics/'.self::TOPIC,
                    'subscriptionName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/subscriptions/'.self::SUBCRIPTION,
                    'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
                ],
                'status' => 'HTTP_CREATED',
            ],
        ]));

        return;
    }

    public function testPublishUnknowType(): void
    {
        $response = self::$messagePublisher->publishMessage('message pus sub', 'is-not-pull-or-push-type', self::TOPIC, self::SUBCRIPTION, self::$pubSubService);
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'code' => Response::HTTP_CONFLICT,
            'message' => 'This type used : "is-not-pull-or-push-type", is not a valid type use push or pull.',
            'status' => 'HTTP_CONFLICT',
        ]));

        return;
    }

    public function testPublishTopicAndSubsciptionWithNotValidFormat(): void
    {
        $response = self::$messagePublisher->publishMessage('message pus sub', 'pull', 'topic name', self::SUBCRIPTION, self::$pubSubService);
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'The Resource name (topic or subscription) has not a valid format see https://cloud.google.com/pubsub/docs/admin#resource_names for more information.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        $response = self::$messagePublisher->publishMessage('message pus sub', 'pull', self::TOPIC, 'subscription name', self::$pubSubService);
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'The Resource name (topic or subscription) has not a valid format see https://cloud.google.com/pubsub/docs/admin#resource_names for more information.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        return;
    }
}
