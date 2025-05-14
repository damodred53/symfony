<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('tweets/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/tweets', name: 'app_tweets_list', methods: ['GET'])]
    public function list(): Response
    {
        $posts = [
            [
                'id' => 1,
                'author' => 'Alice',
                'content' => 'Premier post de test.',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'author' => 'Bob',
                'content' => 'Deuxième post de test.',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        ];

        return $this->render('home/tweet.html.twig', [
            'posts' => $posts,
        ]);
    }
}
