<?php

namespace App\Processor\Grabber;

use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GrabberProcessor implements Processor, TopicSubscriberInterface
{
    private HttpClientInterface $client;
    private ValidatorInterface $validator;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $client, ValidatorInterface $validator, CacheInterface $cache)
    {
        $this->client = $client;
        $this->validator = $validator;
        $this->cache = $cache;
    }

    public function process(Message $message, Context $context)
    {
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($message->getProperty('id'));
        if ($cacheItem->isHit() === true) {
            return self::ACK;
        }

        $violations = $this->validator->validate($message->getProperty('url'), [new Assert\Url()]);
        if (count($violations) > 0) {
            $cacheItem->set(['error' => $violations->get(0)->getMessage()]);
            $this->cache->save($cacheItem);

            return self::REJECT;
        }

        try {
            $response = $this->client->request('GET', $message->getProperty('url'));
            $content = $response->getContent(false);
        } catch (\Throwable $e) {
            $cacheItem->set(['error' => $e->getMessage()]);
            $this->cache->save($cacheItem);

            return self::REJECT;
        }
        $crawler = new Crawler($content);

        $cacheItem->set([
            'html' => $crawler->filter('html')->count(),
            'head' => $crawler->filter('head')->count(),
            'body' => $crawler->filter('body')->count(),
            'p' => $crawler->filter('p')->count(),
            'img' => $crawler->filter('img')->count(),
        ]);
        $this->cache->save($cacheItem);

        return self::ACK;
    }

    public static function getSubscribedTopics()
    {
        return ['grabberTopic'];
    }
}
