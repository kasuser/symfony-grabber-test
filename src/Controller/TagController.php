<?php

namespace App\Controller;

use Enqueue\Client\ProducerInterface;
use Psr\Log\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function post(Request $request, ValidatorInterface $validator, ProducerInterface $producer): Response
    {
        $url = $request->get('url');

        $violations = $validator->validate($url, [new Assert\Url()]);

        if (count($violations) > 0) {
            throw new InvalidArgumentException($violations->get(0)->getMessage());
        }

        $producer->sendEvent('grabberTopic', $url);

        return $this->json([
            'message' => 'OK',
        ]);
    }
}
