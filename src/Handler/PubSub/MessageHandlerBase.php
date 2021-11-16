<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Handler\PubSub;

use GoogleCloudQueueProcess\Service\PubSub\PubSubService;
use Google\Cloud\PubSub\Message;

/**
 * PubSub message handler, to process message.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method __invoke Processing PubSub message.
 * @method processedData Processing data.
 * @method setSubscription Set subscription.
 * @method getSubscription Get subscription.
 * @method setTopic Set topic.
 * @method getTopic Get topic.
 *
 * @version 1.2.0
 * @since 1.0.0
 **/
class MessageHandlerBase
{
    /**
     * @var PubSubService
     **/
    private $_pubSubService;

    /**
     * @var string
     **/
    private $_topic;

    /**
     * @var string
     **/
    private $_subscription;

    public function __construct(PubSubService $pubSubService)
    {
        $this->_pubSubService = $pubSubService;
    }

    /**
     * Processing PubSub message.
     *
     * @param mixed $pubsubMessage
     *
     * @return void
     *
     * @version 1.1.0
     * @since 1.0.0
     **/
    public function __invoke(string $type, $pubsubMessage)
    {
        if ('push' === $type) {
            $decodeMessage = json_decode($pubsubMessage, true);
            if ($decodeMessage) {
                ini_set('memory_limit', '-1');

                return $this->processedData($decodeMessage);
            }
        } elseif ('pull' === $type) {
            $decodeMessage = json_decode($pubsubMessage->data(), true);
            $this->_pubSubService->deleteMessage($this->getSubscription(), $pubsubMessage);

            try {
                if ($decodeMessage) {
                    ini_set('memory_limit', '-1');

                    return $this->processedData($decodeMessage);
                }
            } catch (\Exception $e) {
                $this->_pubSubService->publishMessage($this->getTopic(), $pubsubMessage->data());
                throw new \Exception('Error in processedData method, this message is publish again with type "pull"');
            }
        } else {
            throw new \Exception('Invalid type : "failedType", use "pull" or "push".');
        }
    }

    /**
     * Processing data.
     *
     * @version 1.1.0
     * @since 1.0.0
     **/
    public function processedData(array $data)
    {
        return $data;
    }

    /**
     * Set subscription.
     *
     * @return MessageHandlerBase
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function setSubscription(string $subscription): ?MessageHandlerBase
    {
        $this->_subscription = $subscription;

        return $this;
    }

    /**
     * Get subscription.
     *
     * @return string
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function getSubscription(): ?string
    {
        return $this->_subscription;
    }

    /**
     * Set topic.
     *
     * @return MessageHandlerBase
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function setTopic(string $topic): ?MessageHandlerBase
    {
        $this->_topic = $topic;

        return $this;
    }

    /**
     * Get topic.
     *
     * @return string
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function getTopic(): ?string
    {
        return $this->_topic;
    }
}
