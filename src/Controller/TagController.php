<?php

namespace App\Controller;

use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class TagController extends AbstractController
{
    /**
     * @Route("/tags/{id}", name="tags_get_by_id", methods={"GET"})
     */
    public function getById(string $id, CacheInterface $cache): Response
    {
        /** @var CacheItem $cacheItem */
        $cacheItem = $cache->getItem($id);

        if ($cacheItem->isHit() === false) {
            return $this->json(['error' => 'Job not found or not finished']);
        }

        return $this->json($cacheItem->get());
    }

    /**
     * @Route("/tags", name="tags_post", methods={"POST"})
     */
    public function post(Request $request, ProducerInterface $producer): Response
    {
        $url = $request->get('url');

        $jobId = Uuid::uuid4()->toString();

        $message = new Message('Message body', ['id' => $jobId, 'url' => $url], []);
        $message->setMessageId($jobId);

        $producer->sendEvent('grabberTopic', $message);

        return $this->json(['data' => [
            'job_id' => $jobId,
        ]]);
    }
}
