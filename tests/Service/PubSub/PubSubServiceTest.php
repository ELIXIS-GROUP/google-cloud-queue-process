<?php

declare(strict_types=1);

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GcpQueueProcessTest\Service\PubSub;

use PHPUnit\Framework\TestCase;
use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
use Symfony\Component\HttpFoundation\Response;
use GcpQueueProcessTest\TestConfig;

/**
 * Test class PubSubService.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method setUpBeforeClass
 * @method testPublishPushMessage
 * @method testPublishPushMessageIfSubsciptionNotExist
 * @method testCreateTopicAndPullSubscription
 * @method testCreateTopicAndPullSubscriptionIfTopicExist
 * @method testCreateTopicAndPullSubscriptionIfSubscriptionExist;
 * @method testCreateTopicAndPushSubscription
 * @method testCreateTopicAndPushSubscriptionIfSubscriptionNotExist
 * @method testCreateTopicWithNotValidFormat
 * @method testCreateSubscriptionWithNotValidFormat
 * @method tearDownAfterClass
 *
 * @version 1.2.1
 * @since 1.1.0
 **/
class PubSubServiceTest extends TestCase
{
    /**
     * @var string
     **/
    public const TOPIC = 'test-topic';

    /**
     * @var string
     **/
    public const SUBCRIPTION = 'test-subscription';

    /**
     * @var PubSubService
     **/
    protected static $pubSubService;

    /**
     * @var string
     **/
    protected static $randomPushSubscriptionName;

    /**
     * @var string
     **/
    protected static $randomTopicNameIfSubscriptionNotExist;

    /**
     * @var string
     **/
    protected static $randomTopicNameIfSubscriptionExist;

    public static function setUpBeforeClass(): void
    {
        self::$pubSubService = new PubSubService($_ENV['GOOGLE_PROJECT_ID'], TestConfig::GOOGLE_KEYFILEPATH);
        self::$randomPushSubscriptionName = 'test-push-subscription-'.uniqid();
        self::$randomTopicNameIfSubscriptionNotExist = 'test-push-topic-'.uniqid();
        self::$randomTopicNameIfSubscriptionExist = 'test-pull-topic-'.uniqid();

        return;
    }

    /**
     * Test create topic and pull subscription.
     * Wait an object PubSub Service, who contain data "info" on pubsub action result.
     *
     * @since 1.2.1
     * @version 1.1.0
     **/
    public function testCreateTopicAndPullSubscription(): void
    {
        $response = self::$pubSubService->createTopicAndSubscription('test-pull-topic', 'test-pull-subscription', 'pull');

        $this->assertInstanceOf(PubSubService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertEquals([
            'info' => [
                'code' => 201,
                'message' => [
                    'topicName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/topics/test-pull-topic',
                    'subscriptionName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/subscriptions/test-pull-subscription',
                    'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
                ],
                'status' => 'HTTP_CREATED',
            ],
        ], $response->getInfo());

        return;
    }

    /**
     * Test create topic and pull subscription (topic and subscription already exist for test).
     * Wait a CONFLICT_HTTP exception, because topic already exist in GCP platform for this project.
     *
     * @since 1.1.0
     * @version 1.1.0
     **/
    public function testCreateTopicAndPullSubscriptionIfTopicExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Topic : "test-pull-topic" already exist.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        $response = self::$pubSubService->createTopicAndSubscription('test-pull-topic', 'test-pull-subscription', 'pull');

        return;
    }

    /**
     * Test create topic and pull subscription (subscription already exist for test).
     * Wait a CONFLICT_HTTP exception, because topic already exist in GCP platform for this project.
     *
     * @since 1.1.0
     * @version 1.1.0
     **/
    public function testCreateTopicAndPullSubscriptionIfSubscriptionExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Subscription : "test-pull-subscription" already exist.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        $response = self::$pubSubService->createTopicAndSubscription(self::$randomTopicNameIfSubscriptionExist, 'test-pull-subscription', 'pull');

        return;
    }

    /**
     * Test create topic and push subscription (subscription already exist for test).
     * Wait an object PubSub Service, who contain data "info" on pubsub action result.
     *
     * @since 1.2.1
     * @version 1.1.0
     **/
    public function testCreateTopicAndPushSubscription(): void
    {
        $randomTopicName = 'test-push-topic-'.uniqid();
        $response = self::$pubSubService->createTopicAndSubscription($randomTopicName, 'test-push-subscription', 'push');

        $this->assertInstanceOf(PubSubService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertEquals([
            'info' => [
                'code' => 201,
                'message' => [
                    'topicName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/topics/'.$randomTopicName,
                    'subscriptionName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/subscriptions/test-push-subscription',
                    'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
                ],
                'status' => 'HTTP_CREATED',
            ],
        ], $response->getInfo());

        self::$pubSubService->deleteTopic($randomTopicName);

        return;
    }

    /**
     * Test create topic and push subscription.
     * Wait a CONFLICT_HTTP exception, because subcritption is not create dynamicaly from script PubSubService.
     *
     * @since 1.2.1
     * @version 1.1.0
     **/
    public function testCreateTopicAndPushSubscriptionIfSubscriptionNotExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Subscription : "'.self::$randomPushSubscriptionName.'" not exist, add a new subscription in the GCP Console by specify the push method.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        $response = self::$pubSubService->createTopicAndSubscription(self::$randomTopicNameIfSubscriptionNotExist, self::$randomPushSubscriptionName, 'push');

        return;
    }

    /**
     * Test publish a new "push" message, (topic and subscription already exist for test).
     * Wait an object PubSub Service, who contain data "info" on pubsub action result.
     *
     * @since 1.2.1
     * @version 1.1.0
     **/
    public function testPublishPushMessage(): void
    {
        $response = self::$pubSubService->publishPushMessage('test-push-topic', 'test-push-subscription', 'Pubsub push message.');

        $this->assertInstanceOf(PubSubService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertEquals([
            'info' => [
                'code' => 201,
                'message' => [
                    'topicName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/topics/test-push-topic',
                    'subscriptionName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/subscriptions/test-push-subscription',
                    'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
                ],
                'status' => 'HTTP_CREATED',
            ],
        ], $response->getInfo());

        return;
    }

    /**
     * Test publish a new "push" message, (subscription not exist for test).
     * Wait a CONFLICT_HTTP exception, because subscription not exist in GCP platform for this project.
     *
     * @since 1.1.0
     * @version 1.1.0
     **/
    public function testPublishPushMessageIfSubsciptionNotExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Subscription : "test-subscription-push" not exist, add a new subscription in the GCP Console by specify the push method.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));

        $response = self::$pubSubService->publishPushMessage(self::TOPIC, self::SUBCRIPTION.'-push', 'Pubsub push message.');

        return;
    }

    /**
     * @since 1.2.1
     * @version 1.1.0
     **/
    public function testPublishPullMessage(): void
    {
        $response = self::$pubSubService->publishPullMessage('test-pull-topic', 'test-pull-subscription', 'Pubsub pull message.');

        $this->assertInstanceOf(PubSubService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertEquals([
            'info' => [
                'code' => 201,
                'message' => [
                    'topicName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/topics/test-pull-topic',
                    'subscriptionName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/subscriptions/test-pull-subscription',
                    'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
                ],
                'status' => 'HTTP_CREATED',
            ],
        ], $response->getInfo());

        return;
    }

    /**
     * @since 1.2.1
     * @version 1.1.0
     **/
    public function testPublishPullMessageIfTopicAndSubsciptionNotExist(): void
    {
        $response = self::$pubSubService->publishPullMessage('topic-test-publish-pull-message', 'subscription-test-publish-pull-message', 'Pubsub pull message.');

        $this->assertInstanceOf(PubSubService::class, $response);
        $this->assertIsArray($response->getInfo());
        $this->assertEquals([
            'info' => [
                'code' => 201,
                'message' => [
                    'topicName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/topics/topic-test-publish-pull-message',
                    'subscriptionName' => 'projects/'.$_ENV['GOOGLE_PROJECT_ID'].'/subscriptions/subscription-test-publish-pull-message',
                    'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
                ],
                'status' => 'HTTP_CREATED',
            ],
        ], $response->getInfo());

        return;
    }

    /**
     * Test if topic has not a valid format and return CONFLICT_HTTP exception.
     * Wait a CONFLICT_HTTP exception, if format is not valid.
     *
     * @since 1.1.0
     * @version 1.1.0
     **/
    public function testCreateTopicWithNotValidFormat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'The Resource name (topic or subscription) has not a valid format see https://cloud.google.com/pubsub/docs/admin#resource_names for more information.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));
        $response = self::$pubSubService->createTopicAndSubscription('topic format failure', self::SUBCRIPTION, 'pull');

        return;
    }

    /**
     * Test if subscription has not a valid format and return CONFLICT_HTTP exception.
     * Wait a CONFLICT_HTTP exception, if format is not valide.
     *
     * @since 1.1.0
     * @version 1.1.0
     **/
    public function testCreateSubscriptionWithNotValidFormat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(json_encode([
            'error' => [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'The Resource name (topic or subscription) has not a valid format see https://cloud.google.com/pubsub/docs/admin#resource_names for more information.',
                'status' => 'HTTP_CONFLICT',
            ],
        ]));
        $response = self::$pubSubService->createTopicAndSubscription(self::TOPIC, 'subscription format failure', 'pull');

        return;
    }

    public static function tearDownAfterClass(): void
    {
        self::$pubSubService->deleteSubscription('test-pull-subscription');
        self::$pubSubService->deleteTopic('test-pull-topic');

        self::$pubSubService->deleteTopic('topic-test-publish-pull-message');
        self::$pubSubService->deleteSubscription('subscription-test-publish-pull-message');

        self::$pubSubService->deleteTopic(self::$randomTopicNameIfSubscriptionExist);
        self::$pubSubService->deleteTopic(self::$randomTopicNameIfSubscriptionNotExist);

        return;
    }
}
