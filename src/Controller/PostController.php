<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/posts', name: 'app_posts_list', methods: ['GET'])]
    public function list(): JsonResponse
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

        return $this->json($posts);
    }
}
