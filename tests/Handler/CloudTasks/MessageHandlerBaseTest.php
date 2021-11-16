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
use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksMessageService;
use PHPUnit\Framework\TestCase;
use GoogleCloudQueueProcess\Handler\CloudTasks\MessageHandlerBase;
use GcpQueueProcessTest\TestConfig;

/**
 * Test class MessageHandlerBase.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @version 1.2.1
 * @since 1.1.0
 **/
class MessageHandlerBaseTest extends TestCase
{
    /**
     * @var CloudTasksService
     **/
    protected static $cloudTasksService;

    public static function setUpBeforeClass(): void
    {
        self::$cloudTasksService = new CloudTasksService($_ENV['GOOGLE_PROJECT_ID'], TestConfig::GOOGLE_KEYFILEPATH, TestConfig::GOOGLE_CLOUDTASKS_LOCATION_ID);

        return;
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testInvokeTypePush(): void
    {
        $stub = $this->createMock(CloudTasksMessageService::class);
        $stub->method('getContent')
             ->willReturn(json_encode(['data' => 'message response queue message for test.']));

        $messageHandler = new MessageHandlerBase();
        $response = $messageHandler($stub);

        $this->assertIsArray($response);
        $this->assertEquals(['data' => 'message response queue message for test.'], $response);
    }
}
