<?php

declare(strict_types=1);

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest\Handler\CloudTasks;

use GoogleCloudQueueProcess\Handler\CloudTasks\MessageConsumerHandler;
use GoogleCloudQueueProcess\Handler\CloudTasks\MessageHandlerBase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test class MessageConsumerHandler.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.1.0
 * @since 1.1.0
 **/
class MessageConsumerHandlerTest extends TestCase
{
    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testConsumer(): void
    {
        $stub = $this->createMock(MessageHandlerBase::class);
        $stub->method('__invoke')
             ->willReturn(['data' => 'message test CloudTasks, (invoke return).']);

        $messageConsumer = new MessageConsumerHandler();
        $response = $messageConsumer->consumer(json_encode(['data' => 'message test CloudTasks.']), $stub);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'code' => Response::HTTP_OK,
            'message' => ['data' => 'message test CloudTasks, (invoke return).'],
            'status' => 'HTTP_OK',
        ]));

        return;
    }

    /**
     * Test if method consumer return response HTTP_CONFLICT (409) if catch Exception.
     *
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testConsumerCatchException(): void
    {
        $stub = $this->createMock(MessageHandlerBase::class);
        $stub->method('__invoke')
             ->willReturn(static::throwException(new \Exception('Invoke return exception message.')));

        $messageConsumer = new MessageConsumerHandler();
        $response = $messageConsumer->consumer(json_encode(['data' => 'message response queue message for test.']), $stub);

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString($response->getContent(), json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Invoke return exception message.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        return;
    }
}
