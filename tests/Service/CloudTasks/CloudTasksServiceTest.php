<?php

declare(strict_types=1);

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest\Service\PubSub;

use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksService;
use GcpQueueProcessTest\TestConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test class CloudTasksService.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.2.1
 * @since 1.1.0
 **/
class CloudTasksServiceTest extends TestCase
{
    /**
     * @var PubSubService
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
    public function testCreateCloudTasksIfQueueNotExist(): void
    {
        $response = self::$cloudTasksService->createTask(json_encode(['data' => 'Message cloud tasks.']), self::$uniqtaskname, '/url-test-callback');
        $this->assertInstanceOf(CloudTasksService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertArrayHasKey('info', $response->getInfo());
        $this->assertArrayHasKey('code', $response->getInfo()['info']);
        $this->assertArrayHasKey('message', $response->getInfo()['info']);
        $this->assertArrayHasKey('status', $response->getInfo()['info']);
        $this->assertArrayHasKey('queueName', $response->getInfo()['info']['message']);
        $this->assertArrayHasKey('taskName', $response->getInfo()['info']['message']);
        $this->assertArrayHasKey('projectId', $response->getInfo()['info']['message']);

        return;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testCreateCloudTasks(): void
    {
        $response = self::$cloudTasksService->createTask(json_encode(['data' => 'Message cloud tasks.']), self::$uniqtaskname, '/url-test-callback');
        $this->assertInstanceOf(CloudTasksService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertArrayHasKey('info', $response->getInfo());
        $this->assertArrayHasKey('code', $response->getInfo()['info']);
        $this->assertArrayHasKey('message', $response->getInfo()['info']);
        $this->assertArrayHasKey('status', $response->getInfo()['info']);
        $this->assertArrayHasKey('queueName', $response->getInfo()['info']['message']);
        $this->assertArrayHasKey('taskName', $response->getInfo()['info']['message']);
        $this->assertArrayHasKey('projectId', $response->getInfo()['info']['message']);

        return;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testFailureCreateCloudTasksWithInvalidUri(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Invalid relative uri. The relative uri must begin with \'/\'. No spaces are allowed and the maximum length allowed is 2083 characters.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));
        $response = self::$cloudTasksService->createTask(json_encode(['data' => 'Message cloud tasks.']), 'taskname', 'http://google.com/url-test-callback');

        return;
    }

    public static function tearDownAfterClass(): void
    {
        self::$cloudTasksService->deleteQueue(self::$uniqtaskname);

        return;
    }
}
