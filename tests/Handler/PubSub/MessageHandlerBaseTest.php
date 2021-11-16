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
use GoogleCloudQueueProcess\Handler\PubSub\MessageHandlerBase;
use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
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
    public function testInvokeTypePush(): void
    {
        $messageHandler = new MessageHandlerBase(self::$pubSubService);
        $response = $messageHandler('push', json_encode(['data' => 'message response queue message for test.']));

        $this->assertIsArray($response);
        $this->assertEquals(['data' => 'message response queue message for test.'], $response);
    }

    /**
     * @version 1.1.0
     * @since 1.1.0
     **/
    public function testInvokeTypeFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid type : "failedType", use "pull" or "push".');

        $messageHandler = new MessageHandlerBase(self::$pubSubService);
        $messageHandler('failedType', json_encode([1, 2, 3]));
    }
}
