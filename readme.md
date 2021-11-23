# Google queue process for php (v1.2.4)

gcp-queue-process is an app to interact with the lib [google-cloud-php-pubsub](https://github.com/googleapis/google-cloud-php-pubsub), and now the lib implement the service [google-cloud-php-tasks](https://github.com/googleapis/google-cloud-php-tasks) to use GCP cloudTasks.

### PubSub
This lib make available many method to publish push or pull message. If you use method push or pull do you have the possibility to dynamically create new "topic" and new "subscription" ( create subscription are only available for pull method). See documentation to learn more.

### CloudTasks
gcp-queue-process also provides a method to post a message to CloudTasks. If you are using CloudTask, it is possible to create a new queue from this tool during the message publishing process.   

## Installation

To begin, install the preferred dependency manager for PHP, [Composer](https://getcomposer.org/).    

Install this component with composer
```
$ composer require elixis-group/google-cloud-queue-process
```

## Sample to publish message
### Publish PubSub message
```
require_once __DIR__ . '/vendor/autoload.php';

use GoogleCloudQueueProcess\Handler\PubSub\MessagePublisherHandler;
use GoogleCloudQueueProcess\Service\PubSub\PubSubService;

$messagePublisher = new MessagePublisherHandler();
$pubSubService 	  = new PubSubService();

$content      = json_encode(["blue", "green", "yellow", "red"]);
$type         = "pull"; //use value "push" to use push message
$topic        = "topic-name";
$subscription = "subsciption-name";

$messagePublisher->publishMessage( $content, $type, $topic, $subscription, $pubSubService);

```

### Publish CloudTasks message
```
require_once __DIR__ . '/vendor/autoload.php';

use GoogleCloudQueueProcess\Handler\CloudTasks\MessagePublisherHandler;
use GoogleCloudQueueProcess\Service\CloudTasks\CloudTasksService;

$messagePublisher  = new MessagePublisherHandler();
$cloudTasksService = new CloudTasksService();

$message        = json_encode(["blue", "green", "yellow", "red"]);
$queuename      = "queuename";
$urlTaskHandler = "/task-handler-url";

$messagePublisher->publishMessage( $message, $queuename, $urlTaskHandler, $cloudTasksService);
```

## Sample to consume message
For process your data, create a new class who extends the class MessageHandler Base for Pub Sub or Cloud Tasks and contain a method "processedData".    
This method conten your code for processed your message.

```
namespace ACME;

use GoogleCloudQueueProcess\Handler\PubSub\MessageHandlerBase;

class ACMEMessageHandler extends MessageHandlerBase{

	public function processedData( Array $data )
	{
		//Add your script to process the message.
	}

}

```

### Consume PubSub message
```
require_once __DIR__ . '/vendor/autoload.php';

use GoogleCloudQueueProcess\Handler\PubSub\MessageConsumerHandler;
use ACME\ACMEMessageHandler;

$messageConsumer = new MessageConsumerHandler();
$messageHandler  = new ACMEMessageHandler();

//use "tokenEmail" and "tokenAudience" define in subsciption config.

$request              = file_get_contents('php://input');
$tokenEmail           = "acme@serviceaccount.iam.gserviceaccount.com";
$tokenAudience        = "http://acme-app.com/url";
$googleCredentialPath = "acme@serviceaccount.iam.gserviceaccount.com.json";

$messageRequest  = $messageConsumer->getRequestContent($request, $tokenEmail, $tokenAudience, $googleCredentialPath);


$messageConsumer->pushConsumer($messageRequest, $messageHandler);

```

### Consume CloudTasks message
```
require_once __DIR__ . '/vendor/autoload.php';

use GoogleCloudQueueProcess\Handler\CloudTasks\MessageConsumerHandler;
use ACME\ACMEMessageHandler;

$messageConsumer = new MessageConsumerHandler();
$messageHandler  = new ACMEMessageHandler();

$request = file_get_contents('php://input');

$messageRequest  = $messageConsumer->getRequestContent($request);

$messageConsumer->pushConsumer($messageRequest, $messageHandler);

```

## Contribution

If you contribute at this project before push your modif, don't forget execute php-cs-fixer, phpunit and php-stan, to fix errors and code structure.    

_Before execute tests, go to GCP console and create topic "test-push-topic" and the subscription "test-push-subscription" with type "push".
This is necessary to execute correctly the following unit tests.    
Finish test config add your GOOGLE-PROJECT-ID in phpunit.xml._

```
$ vendor/bin/phpunit tests

$ vendor/bin/php-cs-fixer fix --config .php-cs-fixer.php

$ vendor/bin/phpstan analyse src && vendor/bin/phpstan analyse tests
```