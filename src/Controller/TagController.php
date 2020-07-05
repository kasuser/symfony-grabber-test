<?php

namespace App\Controller;

use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @Route("/tags/{id}", name="tags_get_by_id", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function getById(int $id): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TagController.php',
        ]);
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
