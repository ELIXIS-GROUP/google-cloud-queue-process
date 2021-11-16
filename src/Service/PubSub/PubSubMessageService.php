<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\PubSub;

use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\Message;

/**
 * Class for publish or pull message to Pub/Sub.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method publishMessage publish a Pub/Sub message.
 * @method pullMessage   pull a Pub/Sub message.
 * @method deleteMessage delete a Pub/Sub message.
 *
 * @version 1.2.0
 * @since 1.0.0
 **/
trait PubSubMessageService
{
    /**
     * Publish Pub/Sub message.
     *
     * @param string $topicName the Pub/Sub topic name
     * @param string $message   the Pub/Sub message
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function publishMessage(string $topicName, string $message): void
    {
        $topic = $this->pubSubClient()->topic($topicName);
        $topic->publish((new MessageBuilder())->setData($message)->build());
    }

    /**
     * Pull Pub/Sub message.
     * WARNING Don't forget use method deleteMessage after pull message for purge subscription queue.
     *
     * @param string $subscriptionName the Pub/Sub subscription name
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function pullMessage(string $subscriptionName): array
    {
        $messages = [];
        $subscription = $this->pubSubClient()->subscription($subscriptionName);
        $pubSubMessages = $subscription->pull();

        foreach ($pubSubMessages as $key => $pubSubMessage) {
            array_push($messages, $pubSubMessage);
        }

        return $messages;
    }

    /**
     * Delete Pub/Sub message.
     *
     * @param string  $subscriptionName the Pub/Sub subscription name
     * @param Message $message          the Pub/Sub message
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function deleteMessage(string $subscriptionName, Message $message): void
    {
        $subscription = $this->pubSubClient()->subscription($subscriptionName);
        // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
        $subscription->acknowledge($message);
    }
}
