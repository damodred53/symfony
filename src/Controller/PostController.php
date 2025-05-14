<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
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

    #[Route('/api/posts', name: 'app_posts_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/posts',
        description: 'Retourne la liste des posts.',
        summary: 'Liste des Posts',
        tags: ['Posts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des posts réussie',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(    // ✅ Ici il faut OA\Items, pas JsonContent
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'content', type: 'string', example: 'Premier post de test.'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                        ]
                    )
                )
            )
        ]
    )]
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
