<?php

namespace App\Controller;

use App\Dto\Posts\PostDTO;
use App\Dto\Comment\CommentDTO;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/jwt/posts', name: 'api_posts_')]
#[OA\Tag(name: 'Posts')]

// ➔ Groupe Swagger : Posts
final class PostController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne tous les posts publiés.',
        summary: 'Liste des posts',
        security: [['bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des posts réussie',

                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'content', type: 'string', example: 'Premier post de test.'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function list(PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->findAll();

        $data = array_map(fn(Post $post) => (new PostDTO($post))->toArray(), $posts);

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouveau post pour un utilisateur.',
        summary: 'Créer un nouveau post',
        security: [['bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Mon premier post.'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Post créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post created successfully!'),
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? null;

        if (!$content) {
            return $this->json(['error' => 'Content is required'], 400);
        }

        $user = $userRepository->findOneBy([]);

        if (!$user) {
            return $this->json(['error' => 'No user found to assign as author'], 400);
        }

        $post = new Post();
        $post->setContent($content);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setAuthor($user);

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post created successfully!',
            'post' => (new PostDTO($post))->toArray(),
        ], 201);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'show', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne un post spécifique par son ID.',
        summary: 'Voir un post spécifique',
        security: [['bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détail du post',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                        new OA\Property(property: 'content', type: 'string', example: 'Contenu du post.'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function show(Post $post): JsonResponse
    {
        return $this->json((new PostDTO($post))->toArray());
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Modifie un post existant.',
        summary: 'Modifier un post',
        security: [['bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Nouveau contenu du post.'),
                ],
                type: 'object'
            )
        ),
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post modifié avec succès',
            )
        ]
    )]
    public function update(Request $request, Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }

        $post->setCreatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Post updated successfully!',
            'post' => (new PostDTO($post))->toArray(),
        ]);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'],  name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Supprime un post existant.',
        summary: 'Supprimer un post',
        security: [['bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post supprimé avec succès',
            )
        ]
    )]
    public function delete(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post deleted successfully!',
        ]);
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    #[OA\Get(
        description: 'Recherche de posts par mot-clé.',
        summary: 'Rechercher des posts',
        security: [['bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Résultats de la recherche',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'content', type: 'string', example: 'Contenu du post.'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function search(Request $request, PostRepository $postRepository): JsonResponse
    {
        $keyword = $request->query->get('keyword');

        if (!$keyword) {
            return $this->json(['error' => 'Keyword is required'], 400);
        }

        $posts = $postRepository->createQueryBuilder('p')
            ->where('LOWER(p.content) LIKE LOWER(:keyword)')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();

        $data = array_map(fn(Post $post) => (new PostDTO($post))->toArray(), $posts);

        return $this->json($data);
    }

    #[Route('/{id}/with-comments', name: 'show_with_comments', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne un post avec tous ses commentaires associés.',
        summary: 'Voir un post + ses commentaires',
        security: [['bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post + commentaires récupérés',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'post', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'content', type: 'string', example: 'Mon premier post.'),
                            new OA\Property(property: 'author', type: 'string', example: 'test'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-15 12:26:16'),
                        ]),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'author', type: 'string'),
                                new OA\Property(property: 'content', type: 'string'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        ))
                    ]
                )
            )
        ]
    )]
    public function showWithComments(Post $post): JsonResponse
    {
        $commentsData = array_map(fn($comment) => (new CommentDTO($comment))->toArray(), $post->getComments()->toArray());

        return $this->json([
            'post' => (new PostDTO($post))->toArray(),
            'comments' => $commentsData,
        ]);
    }
}
