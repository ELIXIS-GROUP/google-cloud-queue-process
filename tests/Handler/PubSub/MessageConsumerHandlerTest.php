<?php

declare(strict_types=1);

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest\Handler\PubSub;

use GoogleCloudQueueProcess\Handler\PubSub\MessageConsumerHandler;
use GoogleCloudQueueProcess\Handler\PubSub\MessageHandlerBase;
use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
use GcpQueueProcessTest\TestConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test class MessageConsumerHandlerBase.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.2.1
 * @since 1.1.0
 **/
class MessageConsumerHandlerTest extends TestCase
{
    /**
     * @var PubSubService
     **/
    protected static $pubSubService;

    public static function setUpBeforeClass(): void
    {
        self::$pubSubService = new PubSubService($_ENV['GOOGLE_PROJECT_ID'], TestConfig::GOOGLE_KEYFILEPATH);

        return;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testPublishPushMessage(): void
    {
        $stub = $this->createMock(MessageHandlerBase::class);
        $stub->method('__invoke')
             ->willReturn(['data' => 'message response queue message for test (invoke return).']);

        $messageConsumer = new MessageConsumerHandler();
        $response = $messageConsumer->pushConsumer(json_encode(['data' => 'message response queue message for test.']), $stub);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'code' => Response::HTTP_OK,
            'message' => ['data' => 'message response queue message for test (invoke return).'],
            'status' => 'HTTP_OK',
        ]));
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testPublishPullMessage(): void
    {
        $stub = $this->createMock(MessageHandlerBase::class);
        $stub->method('__invoke')
             ->willReturn(['data' => 'message response queue message for test (invoke return).']);

        $messageConsumer = new MessageConsumerHandler();
        $response = $messageConsumer->pushConsumer(json_encode(['data' => 'message response queue message for test.']), $stub);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'code' => Response::HTTP_OK,
            'message' => ['data' => 'message response queue message for test (invoke return).'],
            'status' => 'HTTP_OK',
        ]));
    }

    /**
     * Test if method pushConsumer or pullConsumer return response HTTP_CONFLICT (409) if catch Exception.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testPublishFairelureType(): void
    {
        $stub = $this->createMock(MessageHandlerBase::class);
        $stub->method('__invoke')
             ->willReturn(static::throwException(new \Exception('Invoke return exception message.')));

        $messageConsumer = new MessageConsumerHandler();
        $response = $messageConsumer->pushConsumer(json_encode(['data' => 'message response queue message for test.']), $stub);

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Invoke return exception message.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        $response = $messageConsumer->pullConsumer('test-sub', $stub, self::$pubSubService);

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Invoke return exception message.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));
    }
}
