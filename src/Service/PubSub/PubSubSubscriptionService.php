<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\PubSub;

/**
 * Class for manage topic method for Pub/Sub.
 * @author Anthony Papillaud <apapillaud@elixis.com>
 *
 * @method createSubscription  Creates a Pub/Sub subscription.
 * @method deleteSubscription  Delete a Pub/Sub subscription.
 * @method listSubscription    Lists a Pub/Sub subscription.
 *
 * @version 1.0.0
 * @since 1.0.0
 **/
trait PubSubSubscriptionService
{
    /**
     * Creates a Pub/Sub subscription.
     *
     * @param string $topicName        the Pub/Sub topic name
     * @param string $subscriptionName the Pub/Sub subscription name
     *
     * @return string the new Pub / Sub subscription name that was created
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function createSubscription(string $topicName, string $subscriptionName)
    {
        $topic = $this->pubSubClient()->topic($topicName);
        $subscription = $topic->subscription($subscriptionName);

        $this->setTopicFullName($topicName);
        $this->setSubscriptionFullName($subscriptionName);

        $subscription->create();

        return $subscription->name();
    }

    /**
     * Delete a Pub/Sub subscription.
     *
     * @param string $subscriptionName the Pub/Sub subscription name
     *
     * @return string the new Pub / Sub subscription name that was created
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function deleteSubscription($subscriptionName)
    {
        $subscription = $this->pubSubClient()->subscription($subscriptionName);
        $subscription->delete();

        return $subscription->name();
    }

    /**
     * Lists all Pub/Sub subscriptions.
     *
     * @return string the Pub / Sub subscription list
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function listSubscriptions()
    {
        $subscriptions = [];
        $pubsub = $this->pubSubClient();

        foreach ($pubsub->subscriptions() as $subscription) {
            array_push($subscriptions, $subscription->name());
        }

        return $subscriptions;
    }
}
