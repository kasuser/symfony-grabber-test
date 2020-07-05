<?php

namespace App\Processor\Grabber;

use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

class GrabberProcessor implements Processor, TopicSubscriberInterface
{
    public function process(Message $message, Context $context)
    {
        $a = $message->getBody();

        return self::ACK;
    }

    public static function getSubscribedTopics()
    {
        return ['grabberTopic'];
    }
}