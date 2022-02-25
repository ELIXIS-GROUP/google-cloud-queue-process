<?php

/*
 * This file is part of the google-cloud-queue-process application.
 * (c) Anthony Papillaud <apapillaud@elixis.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoogleCloudQueueProcess\Service\PubSub;

use GoogleCloudQueueProcess\Kernel;
use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\HttpFoundation\Response;

class PubSubService
{
    use PubSubMessageService;
    use PubSubSubscriptionService;
    use PubSubTopicService;

    /**
     * @var string
     **/
    private $_projectId;

    /**
     * @var string
     **/
    private $_keyFilePath;

    /**
     * @var array
     **/
    private $_info = [];

    /**
     * @var string
     **/
    private $_topicFullName = null;

    /**
     * @var string
     **/
    private $_subscriptionFullName = null;

    /**
     * @var boolean
    **/
    private $_verifyMessageTopicExist = true;

    /**
     * @var boolean
    **/
    private $_verifyMessageSubscriptionExist = true;

    /**
     * @var boolean
    **/
    private $_autorizeCreationTopicAndSubscription = true;

    public function __construct(string $projectId, string $keyFilePath = null)
    {
        Kernel::loadDotEnv();

        $this->_projectId = $projectId;
        $this->_keyFilePath = $keyFilePath;
    }

    /**
     * Init Pub/Sub client.
     *
     * @return Google\Cloud\PubSub\PubSubClient
     *
     * @version 1.2.5
     * @since 1.0.0
     **/
    public function pubSubClient(): PubSubClient
    {
        $pubSubConfig = [
            'projectId' => $this->_projectId,
        ];

        if (isset($this->_keyFilePath)) {
            $pubSubConfig['keyFilePath'] = $this->_keyFilePath;
        }

        return new PubSubClient($pubSubConfig);
    }

    /**
     * Create Pub/Sub topic and its subscription.
     *
     * @param string $topicName        the Pub/Sub topic name
     * @param string $subscriptionName the Pub/Sub subscription name
     * @param string $type             the Pub/Sub subscription type name
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function createTopicAndSubscription(string $topicName, string $subscriptionName, string $type): PubSubService
    {
        if (!$this->validName($topicName) || ('pull' == $type && !$this->validName($subscriptionName))) {
            throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'The Resource name (topic or subscription) has not a valid format see https://cloud.google.com/pubsub/docs/admin#resource_names for more information.', 'HTTP_CONFLICT')));
        }

        $topic = $this->pubSubClient()->topic($topicName);

        if (!$topic->exists()) {
            $topic = $this->createTopic($topicName);
            if ($this->getTopicFullName() == $topic) {
                $subscription = $this->pubSubClient()->subscription($subscriptionName);

                if ('pull' == $type) {
                    if (!$subscription->exists()) {
                        $topic = $this->createSubscription($topicName, $subscriptionName);
                    } else {
                        throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Subscription : "'.$subscriptionName.'" already exist.', 'HTTP_CONFLICT')));
                    }
                } else {
                    if (!$subscription->exists()) {
                        throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Subscription : "'.$subscriptionName.'" not exist, add a new subscription in the GCP Console by specify the push method.', 'HTTP_CONFLICT')));
                    }
                }

                $this->setTopicFullName($topicName);
                $this->setSubscriptionFullName($subscriptionName);

                $this->_setInfo();

                return $this;
            } else {
                throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Topic : "'.$topicName.'" not exist, there was an error during topic creation.', 'HTTP_CONFLICT')));
            }
        } else {
            throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Topic : "'.$topicName.'" already exist.', 'HTTP_CONFLICT')));
        }
    }

    /**
     * Publish message to Pub/Sub if and create topic and/or subscription is not exist.
     *
     * @param string $topicName        the Pub/Sub topic name
     * @param string $subscriptionName the Pub/Sub subscription name
     * @param string $message          the Pub/Sub message
     *
     * @version 1.3.0
     * @since 1.0.0
     **/
    public function publishPullMessage(string $topicName, string $subscriptionName, string $message): PubSubService
    {
        if (!$this->validName($topicName) || !$this->validName($subscriptionName)) {
            throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'The Resource name (topic or subscription) has not a valid format see https://cloud.google.com/pubsub/docs/admin#resource_names for more information.', 'HTTP_CONFLICT')), 1);
        }

        if( $this->getVerifyMessageTopicExist() ){
            $topic = $this->pubSubClient()->topic($topicName);
            if (!$topic->exists() && $this->getAutorizeCreationTopicAndSubscription() ) {
                $topic = $this->createTopic($topicName);
                if ($this->getTopicFullName() == $topic) {

                    if( $this->getVerifyMessageSubscriptionExist() ){
                        $subscription = $this->pubSubClient()->subscription($subscriptionName);
                        if (!$subscription->exists() && $this->getAutorizeCreationTopicAndSubscription()) {
                            $this->createSubscription($topicName, $subscriptionName);
                        }
                    }

                } else {
                    throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Topic : "'.$topicName.'" not exist, there was an error during topic creation.', 'HTTP_CONFLICT')), 1);
                }
            } 

        }
          
        if( $this->getVerifyMessageSubscriptionExist() ){
            $subscription = $this->pubSubClient()->subscription($subscriptionName);
            if (!$subscription->exists() && $this->getAutorizeCreationTopicAndSubscription() ) {
                $this->createSubscription($topicName, $subscriptionName);
            }
        }

        $this->setTopicFullName($topicName);
        $this->setSubscriptionFullName($subscriptionName);

        $this->publishMessage($topicName, $message);
        $this->_setInfo();

        return $this;
    }

    /**
     * Publish push message to Pub/Sub.
     *
     * @param string $topicName              the Pub/Sub topic name
     * @param string $subscriptionName       the Pub/Sub subscription name
     * @param string $message                the Pub/Sub message
     *
     * @version 1.3.0
     * @since 1.0.0
     **/
    public function publishPushMessage(string $topicName, string $subscriptionName, string $message): PubSubService
    {
        if( $this->getVerifyMessageTopicExist() ){
            $topic = $this->pubSubClient()->topic($topicName);
            if (!$topic->exists() && $this->getAutorizeCreationTopicAndSubscription() ) {
                $topic = $this->createTopic($topicName);
                if ($this->getTopicFullName() == $topic) {
                    $this->publishPushMessage($topicName, $subscriptionName, $message);
                } else {
                    throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Topic : "'.$topicName.'" not exist, there was an error during topic creation.', 'HTTP_CONFLICT')));
                }
            }
        }

        if( $this->getVerifyMessageSubscriptionExist() ){
            $subscription = $this->pubSubClient()->subscription($subscriptionName);
            if (!$subscription->exists()) {
                throw new \Exception(json_encode($this->_error(Response::HTTP_CONFLICT, 'Subscription : "'.$subscriptionName.'" not exist, add a new subscription in the GCP Console by specify the push method.', 'HTTP_CONFLICT')));
            }
        }

        $this->setTopicFullName($topicName);
        $this->setSubscriptionFullName($subscriptionName);

        $this->publishMessage($topicName, $message);
        $this->_setInfo();

        return $this;
    }

    /**
     * Check if ressource name is valid for create topic or subscription.
     *
     * @see https://cloud.google.com/pubsub/docs/admin#resource_names
     *
     * @param string $ressourceName the Pub/Sub topic name or subscription name
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function validName(string $ressourceName): bool
    {
        $minLength = 3;
        $maxLength = 255;
        $regex = '/^[a-z](?!oog)([a-z0-9-_.~+%]){'.$minLength.','.$maxLength.'}+$/';

        $isValid = (preg_match($regex, $ressourceName)) ? true : false;

        return $isValid;
    }

    /**
     * Set info for Pub/Sub action.
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    private function _setInfo(): PubSubService
    {
        $this->_info = [
            'info' => [
                'code' => Response::HTTP_CREATED,
                'message' => [
                    'topicName' => $this->getTopicFullName(),
                    'subscriptionName' => $this->getSubscriptionFullName(),
                    'projectId' => $this->_projectId,
                ],
                'status' => 'HTTP_CREATED',
            ],
        ];

        return $this;
    }

    /**
     * Get info for Pub/Sub action.
     *
     * @return array return info for Pub/Sub action
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function getInfo(): array
    {
        return $this->_info;
    }

    /**
     * Set error for Pub/Sub action.
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    private function _error(int $httpCode, string $message, string $status): array
    {
        return [
            'error' => [
                'code' => $httpCode,
                'message' => $message,
                'status' => $status,
            ],
        ];
    }

    /**
     * Get pub/sub topic fullname.
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function getTopicFullName(): string
    {
        return $this->_topicFullName;
    }

    /**
     * Set pub/sub topic fullname.
     *
     * @param string $topicName the Pub/Sub topic full name
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function setTopicFullName(string $topicName): PubSubService
    {
        $this->_topicFullName = 'projects/'.$this->_projectId.'/topics/'.$topicName;

        return $this;
    }

    /**
     * Get pub/sub subscription fullname.
     *
     * @version 1.0.0
     *
     * @since 1.0.0
     **/
    public function getSubscriptionFullName(): string
    {
        return $this->_subscriptionFullName;
    }

    /**
     * Set pub/sub subscription fullname.
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function setSubscriptionFullName(string $subscriptionName): PubSubService
    {
        $this->_subscriptionFullName = 'projects/'.$this->_projectId.'/subscriptions/'.$subscriptionName;

        return $this;
    }

    /**
     * Get Google project id name.
     *
     * @version 1.0.0
     * @since 1.0.0
     **/
    public function getProjectId(): string
    {
        return $this->_projectId;
    }

    /**
     * Get verifyMessageTopicExist.
     *
     * @version 1.2.6
     * @since 1.2.6
     **/
    public function getVerifyMessageTopicExist(): string
    {
        return $this->_verifyMessageTopicExist;
    }

    /**
     * Set verifyMessageTopicExist.
     *
     * @version 1.2.6
     * @since 1.2.6
     **/
    public function setVerifyMessageTopicExist(string $verifyMessageTopicExist): PubSubService
    {
        $this->_verifyMessageTopicExist = $verifyMessageTopicExist;

        return $this;
    }

    /**
     * Get verifyMessageSubscriptionExist.
     *
     * @version 1.2.6
     * @since 1.2.6
     **/
    public function getVerifyMessageSubscriptionExist(): string
    {
        return $this->_verifyMessageSubscriptionExist;
    }

    /**
     * Set verifyMessageSubscriptionExist.
     *
     * @version 1.2.6
     * @since 1.2.6
     **/
    public function setVerifyMessageSubscriptionExist(string $verifyMessageSubscriptionExist): PubSubService
    {
        $this->_verifyMessageSubscriptionExist = $verifyMessageSubscriptionExist;

        return $this;
    }

    /**
     * Get AutorizeCreationTopicAndSubscription.
     *
     * @version 1.2.6
     * @since 1.2.6
     **/
    public function getAutorizeCreationTopicAndSubscription(): string
    {
        return $this->_autorizeCreationTopicAndSubscription;
    }

    /**
     * Set AutorizeCreationTopicAndSubscription.
     *
     * @version 1.2.6
     * @since 1.2.6
     **/
    public function SetAutorizeCreationTopicAndSubscription(string $autorizeCreationTopicAndSubscription): PubSubService
    {
        $this->_autorizeCreationTopicAndSubscription = $autorizeCreationTopicAndSubscription;

        return $this;
    }

}
