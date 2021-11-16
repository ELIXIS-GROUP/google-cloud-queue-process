<?php

declare(strict_types=1);

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest\Handler\CloudTasks;

use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksService;
use GoogleCloudQueueProcess\Handler\CloudTasks\MessagePublisherHandler;
use GcpQueueProcessTest\TestConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test class MessagePublisherHandler.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.2.1
 * @since 1.1.0
 **/
class MessagePublisherHandlerTest extends TestCase
{
    /**
     * @var CloudTasksService
     **/
    protected static $cloudTasksService;

    /**
     * @var string
     **/
    protected static $uniqtaskname;

    /**
     * @version 1.2.1
     * @since 1.1.0
     **/
    public static function setUpBeforeClass(): void
    {
        self::$cloudTasksService = new CloudTasksService($_ENV['GOOGLE_PROJECT_ID'], TestConfig::GOOGLE_KEYFILEPATH, TestConfig::GOOGLE_CLOUDTASKS_LOCATION_ID);
        self::$uniqtaskname = uniqid();

        return;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testPublishTasks(): void
    {
        $messagePublisher = new MessagePublisherHandler();
        $response = $messagePublisher->publishtask(json_encode(['data' => 'Message cloud tasks.']), self::$uniqtaskname, '/url-test-callback', self::$cloudTasksService);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('info', $content);
        $this->assertArrayHasKey('code', $content['info']);
        $this->assertArrayHasKey('message', $content['info']);
        $this->assertArrayHasKey('status', $content['info']);
        $this->assertArrayHasKey('queueName', $content['info']['message']);
        $this->assertArrayHasKey('taskName', $content['info']['message']);
        $this->assertArrayHasKey('projectId', $content['info']['message']);

        return;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testPublishTasksFailure(): void
    {
        $messagePublisher = new MessagePublisherHandler();
        $response = $messagePublisher->publishtask(json_encode(['data' => 'Message cloud tasks.']), self::$uniqtaskname, 'http://google.com/url-test-callback', self::$cloudTasksService);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Invalid relative uri. The relative uri must begin with \'/\'. No spaces are allowed and the maximum length allowed is 2083 characters.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]), $response->getContent());

        return;
    }
}
