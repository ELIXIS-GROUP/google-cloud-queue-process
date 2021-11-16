<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\PubSub;

/**
 * Class for manage topic method for pub/sub.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method createTopic  Creates a Pub/Sub topic.
 * @method deleteTopic  Delete a Pub/Sub topic.
 * @method listTopics   Lists a Pub/Sub topic.
 * @method ifTopicExist Test if a Pub/Sub topic exist.
 *
 * @version 1.0.0
 * @since 1.0.0
 **/
trait PubSubTopicService
{
    /**
     * Creates a Pub/Sub topic.
     *
     * @param string $topicName the Pub/Sub topic name
     *
     * @return string the new Pub / Sub topic name that was created
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function createTopic(string $topicName): string
    {
        $pubsub = $this->pubSubClient();
        $topic = $pubsub->createTopic($topicName);

        $this->setTopicFullName($topicName);

        return $topic->name();
    }

    /**
     * Delete a Pub/Sub topic.
     *
     * @param string $topicName the Pub/Sub topic name
     *
     * @return string the new Pub / Sub topic name that was deleted
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function deleteTopic(string $topicName): string
    {
        $pubsub = $this->pubSubClient();
        $topic = $pubsub->topic($topicName);
        $topic->delete();

        return $topic->name();
    }

    /**
     * Lists all Pub/Sub topics.
     *
     * @return string the Pub / Sub subscription list
     *
     * @return array lists all Pub/Sub topics
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function listTopics(): array
    {
        $topics = [];
        $pubsub = $this->pubSubClient();

        foreach ($pubsub->topics() as $topic) {
            array_push($topics, $topic->name());
        }

        return $topics;
    }
}
